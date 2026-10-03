<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::current();

        return view('settings.index', compact('setting'));
    }

    public function backup()
    {
        $database = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');

        $dump = "-- Dalmar Furniture System Database Backup\n";
        $dump .= "-- Generated: ".now()->toDateTimeString()."\n";
        $dump .= "-- Database: {$database}\n\n";
        $dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $tableObjArray = (array) $tableObj;
            $table = reset($tableObjArray);

            $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
            $createTableArray = (array) ($createTable[0] ?? []);
            $createSql = $createTableArray['Create Table'] ?? '';

            $dump .= "-- Table structure for `{$table}`\n";
            $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $dump .= $createSql.";\n\n";

            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $dump .= "-- Dumping data for `{$table}`\n";
                foreach ($rows as $row) {
                    $rowArr = (array) $row;
                    $values = array_map(function ($val) {
                        if (is_null($val)) {
                            return 'NULL';
                        }

                        return "'".addslashes((string) $val)."'";
                    }, $rowArr);

                    $cols = array_map(fn ($c) => "`{$c}`", array_keys($rowArr));
                    $dump .= "INSERT INTO `{$table}` (".implode(', ', $cols).') VALUES ('.implode(', ', $values).");\n";
                }
                $dump .= "\n";
            }
        }

        $dump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'dalmar_backup_'.now()->format('Y_m_d_His').'.sql';

        return response($dump, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:60'],
            'email_notifications' => ['nullable', 'boolean'],
            'low_stock_alerts' => ['nullable', 'boolean'],
        ]);

        $data['email_notifications'] = $request->boolean('email_notifications');
        $data['low_stock_alerts'] = $request->boolean('low_stock_alerts');

        $setting = Setting::current();

        if ($request->hasFile('logo')) {
            if ($setting->logo && file_exists(public_path('uploads/settings/'.$setting->logo))) {
                unlink(public_path('uploads/settings/'.$setting->logo));
            }

            $filename = uniqid('logo_').'.'.$request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move(public_path('uploads/settings'), $filename);
            $data['logo'] = $filename;
        }

        $setting->update($data);

        return back()->with('success', 'Settings have been updated successfully.');
    }
}
