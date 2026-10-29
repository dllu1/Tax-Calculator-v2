<?php

declare(strict_types=1);

/**
 * Apply local patches to vendor/nativephp/electron/resources/js/ so that
 * `php artisan native:serve` and `native:build` work on current Node/Electron.
 *
 * Why this exists:
 *   NativePHP/Electron 1.3.0 ships package.json with `"type": "module"` and
 *   ESM-only deps (electron-store@10, get-port@7, electron-context-menu@4),
 *   while vite's main process build still emits CJS. Node's ESM<->CJS bridge
 *   crashes with `TypeError: cjsPreparseModuleExports` on this combination.
 *
 * Fix: remove `"type": "module"`, downgrade the 3 ESM-only deps to last CJS
 * versions, then nuke node_modules + lockfile and reinstall.
 *
 * It also refreshes the bundled PHP binary (see updatePhpBinary() below).
 *
 * Idempotent: safe to run multiple times. Auto-invoked from composer.json's
 * post-install-cmd / post-update-cmd, or manually: `php scripts/apply-nativephp-patches.php`.
 */

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Process\Process;

$root  = realpath(__DIR__ . '/..');

/**
 * Replace vendor/nativephp/php-bin/bin/{os}/{arch}/php-X.Y.zip with the latest
 * weekly build from NativePHP's R2 bucket.
 *
 * Why this exists:
 *   nativephp/php-bin 1.2.0 (latest Packagist tag) ships stale binaries — its
 *   php-8.5.zip actually contains PHP 8.4.12, vulnerable to PHP2Shell
 *   (CVE-2026-109618 / CVE-2026-109619, fixed in 8.5.12 / 8.4.27). Since
 *   July 2026 new builds are only published to bin.nativephp.com, not tagged.
 *
 * NativePHP picks php-{PHP_MAJOR}.{PHP_MINOR}.zip based on the PHP running
 * artisan, so we refresh that one for the host platform. The download is
 * verified against the manifest's sha256 before it replaces the vendor file.
 * Network failures only warn, so `composer install` still works offline.
 */
function updatePhpBinary(string $root): void
{
    $manifestUrl = 'https://bin.nativephp.com/main/desktop/versions.json';
    $phpVersion  = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

    $machine = strtolower(php_uname('m'));
    $arch    = in_array($machine, ['arm64', 'aarch64'], true) ? 'arm64' : 'x64';
    $os      = match (PHP_OS_FAMILY) {
        'Windows' => 'win',
        'Darwin'  => 'mac',
        default   => 'linux',
    };
    if ($os === 'win') {
        $arch = 'x64'; // php.js always uses win/x64
    }

    $binDir = $root . '/vendor/nativephp/php-bin/bin/' . $os . '/' . $arch;
    $target = $binDir . '/php-' . $phpVersion . '.zip';
    if (!is_dir($binDir)) {
        fwrite(STDERR, "[skip] nativephp/php-bin not installed — $binDir missing\n");
        return;
    }

    $ctx = stream_context_create(['http' => ['timeout' => 30]]);

    echo "[php-bin] Fetching manifest $manifestUrl\n";
    $manifest = json_decode((string) @file_get_contents($manifestUrl, false, $ctx), true);
    $entry    = $manifest['versions'][$phpVersion]["$os-$arch"][0] ?? null;
    if (!is_array($entry) || empty($entry['url']) || empty($entry['sha256'])) {
        fwrite(STDERR, "[warn] No PHP $phpVersion build for $os-$arch in manifest — keeping current php-bin\n");
        return;
    }

    if (is_file($target) && hash_file('sha256', $target) === $entry['sha256']) {
        echo "[php-bin] php-$phpVersion.zip ($os-$arch) already up to date — ok\n";
        return;
    }

    echo "[php-bin] Downloading {$entry['url']}\n";
    $tmp = $target . '.download';
    if (!@copy($entry['url'], $tmp, $ctx)) {
        @unlink($tmp);
        fwrite(STDERR, "[warn] Download failed — keeping current php-$phpVersion.zip (may be vulnerable)\n");
        return;
    }

    if (hash_file('sha256', $tmp) !== $entry['sha256']) {
        @unlink($tmp);
        fwrite(STDERR, "[warn] sha256 mismatch for downloaded php-$phpVersion.zip — discarded\n");
        return;
    }

    if (!@rename($tmp, $target)) {
        @unlink($tmp);
        fwrite(STDERR, "[warn] Could not replace $target\n");
        return;
    }

    echo "[php-bin] Updated php-$phpVersion.zip ($os-$arch, updated {$manifest['updated_at']})\n";
}

updatePhpBinary($root);
$jsDir = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'nativephp'
       . DIRECTORY_SEPARATOR . 'electron' . DIRECTORY_SEPARATOR . 'resources'
       . DIRECTORY_SEPARATOR . 'js';

if (!is_dir($jsDir)) {
    fwrite(STDERR, "[skip] nativephp/electron not installed — vendor JS dir missing\n");
    exit(0);
}

$pkgPath = $jsDir . DIRECTORY_SEPARATOR . 'package.json';
if (!is_file($pkgPath)) {
    fwrite(STDERR, "[error] $pkgPath not found\n");
    exit(1);
}

$pinned = [
    'electron-store'        => '^8.2.0',
    'get-port'              => '^5.1.1',
    'electron-context-menu' => '^3.6.1',
];

echo "[patch] Reading $pkgPath\n";
$raw  = file_get_contents($pkgPath);
$data = json_decode($raw, true);
if (!is_array($data)) {
    fwrite(STDERR, "[error] Invalid JSON in package.json\n");
    exit(1);
}

$changed = false;

if (array_key_exists('type', $data)) {
    unset($data['type']);
    echo "[patch] Removed \"type\" key (was \"module\")\n";
    $changed = true;
} else {
    echo "[patch] \"type\" key already absent — ok\n";
}

if (!isset($data['dependencies']) || !is_array($data['dependencies'])) {
    fwrite(STDERR, "[error] package.json has no dependencies object\n");
    exit(1);
}

foreach ($pinned as $name => $version) {
    $current = $data['dependencies'][$name] ?? null;
    if ($current === $version) {
        echo "[patch] $name already pinned to $version — ok\n";
        continue;
    }
    $data['dependencies'][$name] = $version;
    echo "[patch] Pinned $name -> $version (was " . ($current ?? 'absent') . ")\n";
    $changed = true;
}

if ($changed) {
    $encoded = json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    if ($encoded === false) {
        fwrite(STDERR, "[error] json_encode failed\n");
        exit(1);
    }
    file_put_contents($pkgPath, $encoded . "\n");
    echo "[patch] Wrote patched package.json\n";
} else {
    echo "[patch] No changes needed in package.json\n";
}

/**
 * Recursive directory delete that works on Windows (cmd/PowerShell)
 * because it never shells out to `rm -rf`.
 */
function rrmdir(string $path): void
{
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    if (!is_dir($path)) {
        return;
    }
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        rrmdir($path . DIRECTORY_SEPARATOR . $item);
    }
    @rmdir($path);
}

$toRemove = [
    $jsDir . DIRECTORY_SEPARATOR . 'out',
    $jsDir . DIRECTORY_SEPARATOR . 'node_modules',
    $jsDir . DIRECTORY_SEPARATOR . 'package-lock.json',
];

$removed = [];
foreach ($toRemove as $target) {
    if (file_exists($target) || is_link($target)) {
        rrmdir($target);
        $removed[] = basename($target);
    }
}
if ($removed) {
    echo "[patch] Deleted: " . implode(', ', $removed) . "\n";
} else {
    echo "[patch] Nothing to delete (cache already clean)\n";
}

echo "[npm] Running: npm install --legacy-peer-deps --no-audit --no-fund\n";
echo "[npm] (this can take 3-5 min on first run)\n";
echo str_repeat('-', 60) . "\n";

// On Windows, `npm` is `npm.cmd`. Symfony Process resolves PATH but we hint via array form.
$npmCommand = PHP_OS_FAMILY === 'Windows'
    ? ['npm.cmd', 'install', '--legacy-peer-deps', '--no-audit', '--no-fund']
    : ['npm',     'install', '--legacy-peer-deps', '--no-audit', '--no-fund'];

$process = new Process($npmCommand, $jsDir, null, null, 900); // 15 min timeout
$process->setTty(false); // disable TTY mode for cross-platform stability
$process->run(function (string $type, string $buffer): void {
    // Stream both stdout and stderr to terminal so user sees realtime progress
    echo $buffer;
});

echo str_repeat('-', 60) . "\n";

if (!$process->isSuccessful()) {
    fwrite(STDERR, "[error] npm install failed (exit {$process->getExitCode()})\n");
    fwrite(STDERR, "[hint] Try running manually: cd $jsDir && npm install --legacy-peer-deps --force\n");
    exit(1);
}

// get-port v5 ships `makeRange()` but the vendored bundle imports v7's
// `portNumbers()` named export. Drop a tiny shim script alongside the JS
// skeleton, and chain it into the package's `postinstall` so the alias
// is re-applied automatically on every `npm install` (including the one
// `php artisan native:build` runs internally). Otherwise the .exe would
// ship without the shim and crash at startup with "portNumbers is not a function".
$shimFile = $jsDir . DIRECTORY_SEPARATOR . 'apply-shims.cjs';
$shimSource = <<<'JS'
// Auto-applied by NativePHP patch script.
// Adds portNumbers (v7 API) -> makeRange (v5 API) alias to get-port,
// because we downgraded get-port to v5.x (CJS) but the vendor bundle
// still imports v7's portNumbers named export.
const fs = require('fs');
const path = require('path');

const target = path.join(__dirname, 'node_modules', 'get-port', 'index.js');
if (!fs.existsSync(target)) {
    process.exit(0); // get-port not installed yet — silently no-op
}
let src = fs.readFileSync(target, 'utf8');
if (src.includes('module.exports.portNumbers')) {
    process.exit(0); // already patched
}
src = src.replace(/\s*$/, '')
       + '\n\n// NativePHP patch: v7 portNumbers() === v5 makeRange()\n'
       + 'module.exports.portNumbers = module.exports.makeRange;\n';
fs.writeFileSync(target, src);
console.log('  • patched get-port: added portNumbers alias');
JS;
file_put_contents($shimFile, $shimSource);
echo "[patch] Wrote apply-shims.cjs alongside vendor JS\n";

// Apply immediately to current install
$origCwd = getcwd();
chdir($jsDir);
passthru('node apply-shims.cjs');
chdir($origCwd);

// Chain into postinstall so it survives future `npm install` runs
$pkgRaw  = file_get_contents($pkgPath);
$pkgData = json_decode($pkgRaw, true);
$currentPostinstall = $pkgData['scripts']['postinstall'] ?? '';
$shimInvocation = 'node apply-shims.cjs';
if (strpos($currentPostinstall, 'apply-shims.cjs') === false) {
    $pkgData['scripts']['postinstall'] = ($currentPostinstall ? $currentPostinstall . ' && ' : '')
                                       . $shimInvocation;
    file_put_contents(
        $pkgPath,
        json_encode($pkgData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
    );
    echo "[patch] Chained apply-shims.cjs into npm postinstall\n";
} else {
    echo "[patch] apply-shims.cjs already in postinstall — ok\n";
}

// electron-plugin/dist/server/childProcess.js is loaded directly by Node via
// utilityProcess.fork() (not through the vite bundle). It uses ESM `import`
// syntax. Since we removed "type": "module" from the parent package.json,
// it would be loaded as CJS and crash with SyntaxError. Drop a sub-package.json
// in electron-plugin/dist/server/ that scopes those .js files back to ESM.
$serverDir = $jsDir . DIRECTORY_SEPARATOR . 'electron-plugin'
           . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'server';
if (is_dir($serverDir)) {
    $subPkg = $serverDir . DIRECTORY_SEPARATOR . 'package.json';
    $needed = ['type' => 'module'];
    $write = true;
    if (is_file($subPkg)) {
        $existing = json_decode((string) file_get_contents($subPkg), true);
        if (is_array($existing) && ($existing['type'] ?? null) === 'module') {
            echo "[patch] electron-plugin/dist/server/package.json already ESM — ok\n";
            $write = false;
        }
    }
    if ($write) {
        file_put_contents(
            $subPkg,
            json_encode($needed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
        );
        echo "[patch] Wrote electron-plugin/dist/server/package.json (type: module)\n";
    }
}

// The bundled static-php-cli php.exe ships with OPcache enabled. On Windows it
// fatals at startup with "Opcode handlers are unusable due to ASLR", which kills
// the bundled PHP server and leaves the Electron window blank ("app won't launch").
// Inject opcache.enable=0 into the default ini settings the plugin passes to php.exe.
$serverPhpJs = $jsDir . DIRECTORY_SEPARATOR . 'electron-plugin'
             . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'server'
             . DIRECTORY_SEPARATOR . 'php.js';
if (is_file($serverPhpJs)) {
    $js = (string) file_get_contents($serverPhpJs);
    if (strpos($js, "'opcache.enable'") !== false) {
        echo "[patch] dist/server/php.js already disables OPcache — ok\n";
    } else {
        $needle  = "'openssl.cafile': state.caCert\n    };";
        $replace = "'openssl.cafile': state.caCert,\n"
                 . "        // Disable OPcache: the static php.exe fatals on Windows\n"
                 . "        // (\"Opcode handlers are unusable due to ASLR\") and kills the server.\n"
                 . "        'opcache.enable': '0',\n"
                 . "        'opcache.enable_cli': '0'\n    };";
        if (strpos($js, $needle) !== false) {
            file_put_contents($serverPhpJs, str_replace($needle, $replace, $js));
            echo "[patch] Disabled OPcache in dist/server/php.js (Windows ASLR fix)\n";
        } else {
            fwrite(STDERR, "[warn] Could not locate ini-settings block in dist/server/php.js — skipped OPcache patch\n");
        }
    }
}

patchStartupPerformance($serverPhpJs);

/**
 * Cut app launch time. Every launch used to boot PHP four times in a row
 * before the window could open (~0.5s each with the static php.exe):
 *   1. native:config + native:php-ini — only change between app versions, so
 *      their JSON output is cached per version in the electron-store.
 *   2. `artisan optimize` on EVERY launch (~1.8s) — now only when the app
 *      version changes. Config is excluded from caching: it embeds the
 *      per-launch NATIVEPHP_SECRET / API port, so a version-gated config cache
 *      would go stale on the next launch.
 *   3. The PHP server ran with OPcache off (see ASLR patch above), making every
 *      request ~10x slower. OPcache is now enabled for the long-lived server
 *      only, with a unique opcache.cache_id per launch so no other php.exe
 *      attaches to its shared memory (the cause of the ASLR fatal). If the
 *      server still dies before it is ready, it is retried without OPcache.
 */
function patchStartupPerformance(string $serverPhpJs): void
{
    if (!is_file($serverPhpJs)) {
        return;
    }
    $js = (string) file_get_contents($serverPhpJs);
    $original = $js;

    $patches = [
        'native:config/php-ini cache' => [
            'marker' => 'function cachedArtisanOutput(',
            'edits'  => [
                [
                    "function retrievePhpIniSettings() {",
                    <<<'JS'
// [perf-patch] native:config / native:php-ini only change between app versions.
// Cache their JSON output per version so a normal launch skips two PHP boots.
function cachedArtisanOutput(key, run) {
    return __awaiter(this, void 0, void 0, function* () {
        const store = new Store({ name: 'nativephp' });
        const cacheable = process.env.NODE_ENV !== 'development';
        const cached = store.get(`${key}_cache`);
        if (cacheable && cached && cached.version === app.getVersion()) {
            return { stdout: cached.stdout, stderr: '' };
        }
        const result = yield run();
        if (cacheable) {
            try {
                JSON.parse(result.stdout);
                store.set(`${key}_cache`, { version: app.getVersion(), stdout: result.stdout });
            }
            catch (e) {
                // Not valid JSON — don't cache a broken result.
            }
        }
        return result;
    });
}
function retrievePhpIniSettings() {
JS,
                ],
                [
                    "let command = ['artisan', 'native:php-ini'];\n"
                    . "        if (runningSecureBuild()) {\n"
                    . "            command.unshift(join(appPath, 'build', '__nativephp_app_bundle'));\n"
                    . "        }\n"
                    . "        return yield promisify(execFile)(state.php, command, phpOptions);",
                    "let command = ['artisan', 'native:php-ini'];\n"
                    . "        if (runningSecureBuild()) {\n"
                    . "            command.unshift(join(appPath, 'build', '__nativephp_app_bundle'));\n"
                    . "        }\n"
                    . "        return yield cachedArtisanOutput('php_ini', () => promisify(execFile)(state.php, command, phpOptions));",
                ],
                [
                    "let command = ['artisan', 'native:config'];\n"
                    . "        if (runningSecureBuild()) {\n"
                    . "            command.unshift(join(appPath, 'build', '__nativephp_app_bundle'));\n"
                    . "        }\n"
                    . "        return yield promisify(execFile)(state.php, command, phpOptions);",
                    "let command = ['artisan', 'native:config'];\n"
                    . "        if (runningSecureBuild()) {\n"
                    . "            command.unshift(join(appPath, 'build', '__nativephp_app_bundle'));\n"
                    . "        }\n"
                    . "        return yield cachedArtisanOutput('native_config', () => promisify(execFile)(state.php, command, phpOptions));",
                ],
            ],
        ],
        'version-gated optimize' => [
            'marker' => "'--except=config'",
            'edits'  => [
                [
                    "function shouldOptimize(store) {\n    return process.env.NODE_ENV !== 'development';\n}",
                    "function shouldOptimize(store) {\n"
                    . "    // [perf-patch] Re-cache routes/views/events only when the app version changes.\n"
                    . "    return store.get('optimized_version') !== app.getVersion()\n"
                    . "        && process.env.NODE_ENV !== 'development';\n}",
                ],
                [
                    "let result = callPhpSync(['artisan', 'optimize'], phpOptions, phpIniSettings);",
                    "// [perf-patch] Never cache config: it holds the per-launch NATIVEPHP_SECRET and\n"
                    . "            // API port. Clear any config cache left behind by older app versions.\n"
                    . "            callPhpSync(['artisan', 'config:clear'], phpOptions, phpIniSettings);\n"
                    . "            let result = callPhpSync(['artisan', 'optimize', '--except=config'], phpOptions, phpIniSettings);",
                ],
            ],
        ],
        'OPcache for PHP server' => [
            'marker' => "'opcache.cache_id'",
            'edits'  => [
                [
                    <<<'JS'
        const phpServer = callPhp(['-S', `127.0.0.1:${phpPort}`, serverPath], {
            cwd: cwd,
            env
        }, phpIniSettings);
        const portRegex = /Development Server \(.*:([0-9]+)\) started/gm;
        phpServer.stdout.on('data', (data) => {
            if (parseInt(process.env.SHELL_VERBOSITY) > 0) {
                console.log(data.toString().trim());
            }
        });
        phpServer.stderr.on('data', (data) => {
            const error = data.toString();
            const match = portRegex.exec(data.toString());
            if (match) {
                const port = parseInt(match[1]);
                console.log("PHP Server started on port: ", port);
                resolve({
                    port,
                    process: phpServer,
                });
            }
            else {
                if (error.includes('[NATIVE_EXCEPTION]:')) {
                    let logFile = join(storagePath, 'logs');
                    console.log();
                    console.error('Error in PHP:');
                    console.error('  ' + error.split('[NATIVE_EXCEPTION]:')[1].trim());
                    console.log('Please check your log files:');
                    console.log('  ' + logFile);
                    console.log();
                }
            }
        });
        phpServer.on('error', (error) => {
            reject(error);
        });
        phpServer.on('close', (code) => {
            console.log(`PHP server exited with code ${code}`);
        });
JS,
                    <<<'JS'
        // [perf-patch] OPcache for the long-lived server only (~10x faster requests).
        // A unique cache_id per launch keeps every other php.exe off this shared
        // memory — attaching to it is what triggers the Windows ASLR fatal.
        // If the server still dies before it is ready, retry once without OPcache.
        const opcacheIni = {
            'opcache.enable': '1',
            'opcache.enable_cli': '1',
            'opcache.cache_id': `nativephp-${process.pid}-${Date.now()}`,
        };
        let serverStarted = false;
        const startServer = (useOpcache) => {
            const serverIni = useOpcache ? Object.assign({}, phpIniSettings, opcacheIni) : phpIniSettings;
            const phpServer = callPhp(['-S', `127.0.0.1:${phpPort}`, serverPath], {
                cwd: cwd,
                env
            }, serverIni);
            const portRegex = /Development Server \(.*:([0-9]+)\) started/gm;
            phpServer.stdout.on('data', (data) => {
                if (parseInt(process.env.SHELL_VERBOSITY) > 0) {
                    console.log(data.toString().trim());
                }
            });
            phpServer.stderr.on('data', (data) => {
                const error = data.toString();
                const match = portRegex.exec(data.toString());
                if (match) {
                    serverStarted = true;
                    const port = parseInt(match[1]);
                    console.log("PHP Server started on port: ", port, useOpcache ? '(OPcache on)' : '(OPcache off)');
                    resolve({
                        port,
                        process: phpServer,
                    });
                }
                else {
                    if (error.includes('[NATIVE_EXCEPTION]:')) {
                        let logFile = join(storagePath, 'logs');
                        console.log();
                        console.error('Error in PHP:');
                        console.error('  ' + error.split('[NATIVE_EXCEPTION]:')[1].trim());
                        console.log('Please check your log files:');
                        console.log('  ' + logFile);
                        console.log();
                    }
                }
            });
            phpServer.on('error', (error) => {
                reject(error);
            });
            phpServer.on('close', (code) => {
                console.log(`PHP server exited with code ${code}`);
                if (!serverStarted && useOpcache) {
                    console.warn('PHP server failed to start with OPcache — retrying without it');
                    startServer(false);
                }
            });
        };
        startServer(true);
JS,
                ],
            ],
        ],
    ];

    foreach ($patches as $name => $patch) {
        if (strpos($js, $patch['marker']) !== false) {
            echo "[patch] dist/server/php.js already has $name — ok\n";
            continue;
        }
        foreach ($patch['edits'] as [$needle, $replace]) {
            if (strpos($js, $needle) === false) {
                fwrite(STDERR, "[warn] Could not locate code for $name in dist/server/php.js — skipped\n");
                continue 2;
            }
        }
        foreach ($patch['edits'] as [$needle, $replace]) {
            $js = str_replace($needle, $replace, $js);
        }
        echo "[patch] Applied $name in dist/server/php.js\n";
    }

    if ($js !== $original) {
        file_put_contents($serverPhpJs, $js);
    }
}

echo "[done] NativePHP patches applied.\n";
echo "[done] Next steps:\n";
echo "[done]   1. cp .env.nativephp .env   (or run dev-native.bat)\n";
echo "[done]   2. php artisan native:serve\n";
echo "[done]   3. php artisan native:build win  (when ready to build .exe)\n";
