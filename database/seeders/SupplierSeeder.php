<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Supplier;
use App\Models\SupplierPart;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Fictional but plausible motorsport parts distributors — not real
     * companies, just enough variety to give the ordering workflow real
     * choices between price, location, and delivery cost.
     *
     * @var list<string>
     */
    private const SUPPLIERS = [
        'Trackside Components Ltd',
        'Paddock Supply Co.',
        'Apex Motorsport Logistics',
        'Circuit Parts International',
        'Grid Racing Supplies',
    ];

    /**
     * Seed suppliers and stock every part with 2-4 of them, so any part that
     * ends up "required" in demo data always has real suppliers to order from.
     */
    public function run(): void
    {
        $suppliers = collect(self::SUPPLIERS)->map(fn (string $name) => Supplier::create([
            'name' => $name,
            'contact_email' => 'sales@'.Str::slug($name).'.example',
        ]));

        foreach (Part::all() as $part) {
            foreach ($suppliers->random(fake()->numberBetween(2, 4)) as $supplier) {
                SupplierPart::factory()
                    ->for($supplier)
                    ->for($part)
                    ->inStock(fake()->numberBetween(3, 30))
                    ->create();
            }
        }
    }
}
