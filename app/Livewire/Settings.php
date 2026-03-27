<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\Attributes\Renderless;
use Illuminate\Support\Facades\Auth;

class Settings extends Component
{
    #[Renderless]
    public function savePrices(array $prices): void
    {
        // Only admin and super_admin can change prices
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            return;
        }

        $keys = ['price_basic', 'price_premium', 'price_deluxe', 'price_addon'];
        foreach ($keys as $key) {
            if (isset($prices[$key])) {
                $val = (int) $prices[$key];
                if ($val > 0) {
                    Setting::set($key, $val);
                }
            }
        }

        $this->dispatch('settings-saved');
        // Broadcast new prices directly so Alpine can update without DOM morph
        $newPrices = Setting::packagePrices();
        $this->dispatch('prices-updated', prices: [
            'p199'  => $newPrices['basic'],
            'p269'  => $newPrices['premium'],
            'p349'  => $newPrices['deluxe'],
            'addon' => $newPrices['addon'],
        ]);
    }

    #[Renderless]
    public function factoryReset(): void
    {
        // Super admin only
        if (Auth::user()->role !== 'super_admin') return;

        $driver = \DB::getDriverName();

        // TRUNCATE is a DDL statement in MySQL — it causes an implicit COMMIT and
        // cannot be safely used inside a DB::transaction(). Run them outside first,
        // then wrap the DML updates in a transaction.
        if ($driver === 'mysql') {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            \DB::statement('PRAGMA foreign_keys = OFF');
        }

        Order::truncate();
        StockMovement::truncate();

        if ($driver === 'mysql') {
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            \DB::statement('PRAGMA foreign_keys = ON');
        }

        // Now safely wrap the DML updates in a transaction
        \DB::transaction(function () {
            // Reset all product stock to zero
            Product::query()->update([
                'stock'           => 0,
                'available_from'  => null,
                'available_until' => null,
            ]);

            // Reset prices to defaults
            Setting::set('price_basic',   199);
            Setting::set('price_premium', 269);
            Setting::set('price_deluxe',  349);
            Setting::set('price_addon',   25);
        });

        // Fix #8: Audit log — factory reset wipes all data, so log to file at minimum
        \Log::warning('Factory reset performed', [
            'by_user_id' => Auth::id(),
            'by_user'    => Auth::user()->name,
            'at'         => now()->toDateTimeString(),
        ]);

        $this->dispatch('factory-reset-done');
        $this->dispatch('prices-updated', prices: [
            'p199' => 199, 'p269' => 269, 'p349' => 349, 'addon' => 25,
        ]);
        // Tell Statistics, Inventory, and Receipts to refresh — all data has been wiped
        $this->dispatch('system-reset');
    }


    public function render()
    {
        $prices = Setting::packagePrices();
        return view('livewire.settings', compact('prices'));
    }
}
