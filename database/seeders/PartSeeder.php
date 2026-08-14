<?php

namespace Database\Seeders;

use App\Enums\PartCategory;
use App\Models\Part;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * A realistic catalog of parts spanning every category, so demo data
     * covers the full range a team would actually be tracking.
     *
     * @var list<array{name: string, part_number: string, category: PartCategory, manufacturer: string, description: string}>
     */
    private const CATALOG = [
        ['name' => 'Front Wing Assembly', 'part_number' => 'AER-FW-001', 'category' => PartCategory::Aerodynamics, 'manufacturer' => 'Apex Composites', 'description' => 'Multi-element front wing with adjustable flap for balance tuning.'],
        ['name' => 'Rear Wing Assembly', 'part_number' => 'AER-RW-001', 'category' => PartCategory::Aerodynamics, 'manufacturer' => 'Apex Composites', 'description' => 'DRS-compatible rear wing, low-downforce specification.'],
        ['name' => 'Floor Edge Panel', 'part_number' => 'AER-FL-014', 'category' => PartCategory::Aerodynamics, 'manufacturer' => 'Apex Composites', 'description' => 'Ground-effect floor edge section with vortex generators.'],
        ['name' => 'Diffuser', 'part_number' => 'AER-DF-007', 'category' => PartCategory::Aerodynamics, 'manufacturer' => 'Reynard Aero', 'description' => 'Rear diffuser tuned for high-speed circuits.'],
        ['name' => 'Internal Combustion Engine', 'part_number' => 'PWR-ICE-100', 'category' => PartCategory::Powertrain, 'manufacturer' => 'Mercedes-AMG HPP', 'description' => '1.6L V6 turbocharged hybrid power unit.'],
        ['name' => 'Turbocharger', 'part_number' => 'PWR-TC-022', 'category' => PartCategory::Powertrain, 'manufacturer' => 'Mercedes-AMG HPP', 'description' => 'Single-turbo unit mounted between the cylinder banks.'],
        ['name' => 'MGU-K', 'part_number' => 'PWR-MGUK-009', 'category' => PartCategory::Powertrain, 'manufacturer' => 'Mercedes-AMG HPP', 'description' => 'Kinetic energy recovery motor generator unit.'],
        ['name' => 'MGU-H', 'part_number' => 'PWR-MGUH-004', 'category' => PartCategory::Powertrain, 'manufacturer' => 'Mercedes-AMG HPP', 'description' => 'Heat energy recovery motor generator unit.'],
        ['name' => 'Energy Store (Battery)', 'part_number' => 'PWR-ES-003', 'category' => PartCategory::Powertrain, 'manufacturer' => 'Mercedes-AMG HPP', 'description' => 'Hybrid system energy storage pack.'],
        ['name' => 'Carbon Brake Disc (Front)', 'part_number' => 'BRK-DF-018', 'category' => PartCategory::Brakes, 'manufacturer' => 'Brembo', 'description' => 'Carbon-carbon front brake disc, ventilated.'],
        ['name' => 'Carbon Brake Disc (Rear)', 'part_number' => 'BRK-DR-018', 'category' => PartCategory::Brakes, 'manufacturer' => 'Brembo', 'description' => 'Carbon-carbon rear brake disc, ventilated.'],
        ['name' => 'Brake Caliper (Front)', 'part_number' => 'BRK-CF-006', 'category' => PartCategory::Brakes, 'manufacturer' => 'Brembo', 'description' => 'Six-piston monobloc front brake caliper.'],
        ['name' => 'Brake-by-Wire Actuator', 'part_number' => 'BRK-BBW-002', 'category' => PartCategory::Brakes, 'manufacturer' => 'Bosch Motorsport', 'description' => 'Rear brake-by-wire actuator for hybrid regeneration blending.'],
        ['name' => 'Front Wishbone (Upper)', 'part_number' => 'SUS-WBF-011', 'category' => PartCategory::Suspension, 'manufacturer' => 'Multimatic', 'description' => 'Upper front wishbone, carbon-fibre construction.'],
        ['name' => 'Rear Wishbone (Lower)', 'part_number' => 'SUS-WBR-012', 'category' => PartCategory::Suspension, 'manufacturer' => 'Multimatic', 'description' => 'Lower rear wishbone, carbon-fibre construction.'],
        ['name' => 'Torsion Bar', 'part_number' => 'SUS-TB-005', 'category' => PartCategory::Suspension, 'manufacturer' => 'Multimatic', 'description' => 'Front-axle torsion bar spring element.'],
        ['name' => 'Anti-Roll Bar', 'part_number' => 'SUS-ARB-008', 'category' => PartCategory::Suspension, 'manufacturer' => 'Multimatic', 'description' => 'Adjustable front anti-roll bar.'],
        ['name' => 'Engine Control Unit', 'part_number' => 'ELE-ECU-001', 'category' => PartCategory::Electronics, 'manufacturer' => 'McLaren Applied', 'description' => 'FIA-standard engine and chassis control unit.'],
        ['name' => 'Steering Wheel Display Unit', 'part_number' => 'ELE-SWD-014', 'category' => PartCategory::Electronics, 'manufacturer' => 'McLaren Applied', 'description' => 'Driver display and switch panel integrated into the steering wheel.'],
        ['name' => 'Wiring Loom', 'part_number' => 'ELE-WL-021', 'category' => PartCategory::Electronics, 'manufacturer' => 'McLaren Applied', 'description' => 'Full-car sensor and control wiring loom.'],
        ['name' => 'Survival Cell (Monocoque)', 'part_number' => 'CHA-MC-001', 'category' => PartCategory::Chassis, 'manufacturer' => 'Carbon Revolution Composites', 'description' => 'Carbon-fibre monocoque survival cell.'],
        ['name' => 'Front Impact Structure', 'part_number' => 'CHA-FIS-002', 'category' => PartCategory::Chassis, 'manufacturer' => 'Carbon Revolution Composites', 'description' => 'FIA crash-tested front impact absorption structure.'],
        ['name' => 'Halo', 'part_number' => 'CHA-HAL-003', 'category' => PartCategory::Chassis, 'manufacturer' => 'CP Autosport', 'description' => 'Titanium cockpit protection halo.'],
        ['name' => 'Water Radiator', 'part_number' => 'COO-WR-006', 'category' => PartCategory::Cooling, 'manufacturer' => 'Secan', 'description' => 'Side-pod mounted water cooling radiator.'],
        ['name' => 'Oil Cooler', 'part_number' => 'COO-OC-007', 'category' => PartCategory::Cooling, 'manufacturer' => 'Secan', 'description' => 'Engine oil cooling radiator.'],
        ['name' => 'Gearbox Casing', 'part_number' => 'TRN-GBC-001', 'category' => PartCategory::Transmission, 'manufacturer' => 'Xtrac', 'description' => 'Carbon-fibre 8-speed gearbox casing, load-bearing rear structure.'],
        ['name' => 'Drive Shaft', 'part_number' => 'TRN-DS-009', 'category' => PartCategory::Transmission, 'manufacturer' => 'Xtrac', 'description' => 'Rear axle drive shaft.'],
        ['name' => 'Clutch Assembly', 'part_number' => 'TRN-CL-004', 'category' => PartCategory::Transmission, 'manufacturer' => 'AP Racing', 'description' => 'Carbon multi-plate clutch assembly.'],
    ];

    /**
     * Seed the parts catalog.
     */
    public function run(): void
    {
        foreach (self::CATALOG as $part) {
            Part::create($part);
        }
    }
}
