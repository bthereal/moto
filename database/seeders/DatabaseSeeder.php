<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\VehiclePartStatus;
use App\Enums\VehicleStatus;
use App\Models\Part;
use App\Models\SupplierPart;
use App\Models\Team;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * A handful of real constructors spanning F1 history, each paired with one
     * driver from their current line-up and one from their history, so the
     * demo data reads as a real grid rather than randomly generated names.
     *
     * @var list<array{name: string, full_name: string, base: string, principal: string, founded_year: int, color: string, engine_supplier: string, drivers: list<string>}>
     */
    private const TEAMS = [
        [
            'name' => 'Ferrari',
            'full_name' => 'Scuderia Ferrari',
            'base' => 'Maranello, Italy',
            'principal' => 'Fred Vasseur',
            'founded_year' => 1929,
            'color' => '#DC0000',
            'engine_supplier' => 'Ferrari',
            'drivers' => ['Charles Leclerc', 'Michael Schumacher'],
        ],
        [
            'name' => 'McLaren',
            'full_name' => 'McLaren Formula 1 Team',
            'base' => 'Woking, United Kingdom',
            'principal' => 'Andrea Stella',
            'founded_year' => 1963,
            'color' => '#FF8000',
            'engine_supplier' => 'Mercedes',
            'drivers' => ['Lando Norris', 'Ayrton Senna'],
        ],
        [
            'name' => 'Williams',
            'full_name' => 'Williams Racing',
            'base' => 'Grove, United Kingdom',
            'principal' => 'James Vowles',
            'founded_year' => 1977,
            'color' => '#00A0DE',
            'engine_supplier' => 'Mercedes',
            'drivers' => ['Alex Albon', 'Nigel Mansell'],
        ],
        [
            'name' => 'Mercedes',
            'full_name' => 'Mercedes-AMG Petronas F1 Team',
            'base' => 'Brackley, United Kingdom',
            'principal' => 'Toto Wolff',
            'founded_year' => 1954,
            'color' => '#00D2BE',
            'engine_supplier' => 'Mercedes',
            'drivers' => ['George Russell', 'Juan Manuel Fangio'],
        ],
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'team_id' => null,
            'role' => UserRole::Admin,
        ]);

        $this->call(PartSeeder::class);
        $this->call(SupplierSeeder::class);

        $vehicles = new Collection;

        foreach (self::TEAMS as $teamData) {
            $team = Team::create([
                'name' => $teamData['name'],
                'full_name' => $teamData['full_name'],
                'base' => $teamData['base'],
                'principal' => $teamData['principal'],
                'founded_year' => $teamData['founded_year'],
                'color' => $teamData['color'],
            ]);

            User::factory()->count(3)->role(UserRole::Engineer)->for($team)->create();

            $drivers = collect($teamData['drivers'])->map(fn (string $name) => User::factory()
                ->role(UserRole::Driver)
                ->for($team)
                ->create([
                    'name' => $name,
                    'email' => Str::slug($name).'@'.Str::slug($team->name).'.example',
                ]));

            $teamVehicles = Vehicle::factory()
                ->count(2)
                ->for($team)
                ->state(['engine_supplier' => $teamData['engine_supplier']])
                ->sequence(fn ($sequence) => ['driver_id' => $drivers[$sequence->index]->id])
                ->create();

            $vehicles->push(...$teamVehicles->all());
        }

        $this->seedPartRequirements($vehicles);
    }

    /**
     * Put a handful of vehicles into testing with parts at different stages
     * of the required -> ordered -> in-transit -> delivered -> fitted
     * pipeline, so the "can this vehicle go back to active?" rule — and the
     * ordering workflow itself — have real examples to show.
     */
    private function seedPartRequirements(Collection $vehicles): void
    {
        $parts = Part::inRandomOrder()->get();

        // Blocked: one part still needs ordering, one has already been ordered
        // from a real supplier, one is already in transit.
        $blocked = $vehicles->get(0);
        $blocked->update(['status' => VehicleStatus::Testing]);

        $blocked->vehicleParts()->create([
            'part_id' => $parts->get(0)->id,
            'status' => VehiclePartStatus::Required,
        ]);

        $toOrder = $blocked->vehicleParts()->create([
            'part_id' => $parts->get(1)->id,
            'status' => VehiclePartStatus::Required,
        ]);

        $stockedListing = SupplierPart::where('part_id', $parts->get(1)->id)
            ->where('quantity', '>', 0)
            ->first();

        if ($stockedListing) {
            $toOrder->placeOrder($stockedListing);
        }

        $blocked->vehicleParts()->create([
            'part_id' => $parts->get(2)->id,
            'status' => VehiclePartStatus::InTransit,
        ]);

        // Blocked: closer to ready, but one part has only been delivered, not fitted.
        $almostReady = $vehicles->get(2);
        $almostReady->update(['status' => VehicleStatus::Testing]);
        $almostReady->vehicleParts()->createMany([
            ['part_id' => $parts->get(3)->id, 'status' => VehiclePartStatus::Fitted],
            ['part_id' => $parts->get(4)->id, 'status' => VehiclePartStatus::Delivered],
        ]);

        // Ready: every required part has been fitted, so this one is eligible to reactivate.
        $ready = $vehicles->get(4);
        $ready->update(['status' => VehicleStatus::Testing]);
        $ready->vehicleParts()->createMany([
            ['part_id' => $parts->get(5)->id, 'status' => VehiclePartStatus::Fitted],
            ['part_id' => $parts->get(6)->id, 'status' => VehiclePartStatus::Fitted],
        ]);
    }
}
