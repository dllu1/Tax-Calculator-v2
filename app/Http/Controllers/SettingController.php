<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings)
    {
    }

    public function index()
    {
        // Read-only: merge defaults in-memory so missing rows don't trigger
        // a DB write on GET. Defaults get persisted only by POST handlers
        // (update / brackets.update / reset).
        $tax = $this->settings->settingsForView(Setting::GROUP_TAX);
        $payroll = $this->settings->settingsForView(Setting::GROUP_PAYROLL);

        $brackets = $this->settings->brackets();

        return view('settings.index', compact('tax', 'payroll', 'brackets'));
    }

    /**
     * Cập nhật các tham số đơn (number/string), không bao gồm biểu thuế.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ]);

        // POST handler: materialize any missing default rows before writing.
        $this->settings->ensureDefaults();

        foreach ($data['settings'] as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if (!$setting || $setting->type === Setting::TYPE_JSON) {
                continue;
            }
            if ($setting->type === Setting::TYPE_NUMBER && !is_numeric($value)) {
                continue;
            }
            $this->settings->set($key, $value);
        }

        $msg = __('Đã lưu cấu hình. Bảng lương sẽ tính lại theo công thức mới.');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => $msg]);
        }

        return redirect()->route('settings.index')->with('success', $msg);
    }

    /**
     * Cập nhật biểu thuế lũy tiến từng phần.
     */
    public function updateBrackets(Request $request)
    {
        $data = $request->validate([
            'brackets' => ['required', 'array', 'min:1'],
            'brackets.*.limit' => ['required', 'numeric', 'min:0'],
            'brackets.*.rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'brackets.*.deduction' => ['required', 'numeric', 'min:0'],
        ]);

        $brackets = array_values(array_map(fn ($b) => [
            'limit' => (float) $b['limit'],
            'rate' => (float) $b['rate'],
            'deduction' => (float) $b['deduction'],
        ], $data['brackets']));

        // Sắp xếp tăng dần theo limit, với limit=0 (không giới hạn) ở cuối
        usort($brackets, function ($a, $b) {
            $aLast = $a['limit'] <= 0;
            $bLast = $b['limit'] <= 0;
            if ($aLast && !$bLast) return 1;
            if (!$aLast && $bLast) return -1;
            return $a['limit'] <=> $b['limit'];
        });

        // POST handler: materialize default rows before writing.
        $this->settings->ensureDefaults();
        $this->settings->set('tax.brackets', $brackets);

        $msg = __('Đã cập nhật biểu thuế lũy tiến.');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => $msg, 'brackets' => $brackets]);
        }

        return redirect()->route('settings.index')->with('success', $msg);
    }

    /**
     * Đưa toàn bộ cấu hình về giá trị mặc định.
     */
    public function reset(Request $request)
    {
        // Ensure all default rows exist, then overwrite their values.
        $this->settings->ensureDefaults();

        foreach (SettingService::defaults() as $key => $config) {
            $setting = Setting::where('key', $key)->first();
            if (!$setting) {
                continue;
            }
            $setting->update(['value' => (string) $config['value']]);
        }
        $this->settings->clearCache();

        $msg = __('Đã khôi phục cấu hình mặc định.');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => $msg]);
        }

        return redirect()->route('settings.index')->with('success', $msg);
    }
}
