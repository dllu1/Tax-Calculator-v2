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
 * Idempotent: safe to run multiple times. Auto-invoked from composer.json's
 * post-install-cmd / post-update-cmd, or manually: `php scripts/apply-nativephp-patches.php`.
 */

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Process\Process;

$root  = realpath(__DIR__ . '/..');
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

echo "[done] NativePHP patches applied.\n";
echo "[done] Next steps:\n";
echo "[done]   1. cp .env.nativephp .env   (or run dev-native.bat)\n";
echo "[done]   2. php artisan native:serve\n";
echo "[done]   3. php artisan native:build win  (when ready to build .exe)\n";
