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
        $this->settings->ensureDefaults();

        $tax = Setting::where('group', Setting::GROUP_TAX)->orderBy('id')->get();
        $payroll = Setting::where('group', Setting::GROUP_PAYROLL)->orderBy('id')->get();

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

        return redirect()->route('settings.index')
            ->with('success', 'Đã lưu cấu hình. Bảng lương sẽ tính lại theo công thức mới.');
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

        $this->settings->set('tax.brackets', $brackets);

        return redirect()->route('settings.index')
            ->with('success', 'Đã cập nhật biểu thuế lũy tiến.');
    }

    /**
     * Đưa toàn bộ cấu hình về giá trị mặc định.
     */
    public function reset()
    {
        foreach (SettingService::defaults() as $key => $config) {
            $setting = Setting::where('key', $key)->first();
            if (!$setting) {
                continue;
            }
            $setting->update(['value' => (string) $config['value']]);
        }
        $this->settings->clearCache();

        return redirect()->route('settings.index')
            ->with('success', 'Đã khôi phục cấu hình mặc định.');
    }
}
