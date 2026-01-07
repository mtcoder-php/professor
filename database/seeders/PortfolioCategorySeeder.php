<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PortfolioCategory;

class PortfolioCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Ochiq o\'quv mashg\'ulotlari va mahorat darslarini tashkil etish',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 1
            ],
            [
                'name' => 'Iqtidorli va iste\'dodli talabalar bilan ishlash',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 2
            ],
            [
                'name' => 'Ilmiy konferensiyalarda ma\'ruza bilan qatnashish',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 3
            ],
            [
                'name' => 'Ilmiy jurnallarda maqolalar chop etish',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 4
            ],
            [
                'name' => 'Ko\'rgazma va tanlovlarda ishtirok etish',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 5
            ],
            [
                'name' => 'Ilmiy loyihalarda ishtirok etish',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 6
            ],
            [
                'name' => 'Xalqaro (impakt-faktorli) nashrlarda maqolalar e\'lon qilish',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 7
            ],
            [
                'name' => 'Ixtiro, ratsionalizatorlik takliflari, innovatsion ishlanmalarga mualliflik',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 8
            ],
            [
                'name' => 'Monografiya, mualliflik ijodiy ishlar katalogini tayyorlash va nashr qilish',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 9
            ],
            [
                'name' => 'O\'quv adabiyotlari tayyorlash va nashr qilish',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 10
            ],
            [
                'name' => 'Dissertatsiyaga ilmiy rahbarlik qilish',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 11
            ],
            [
                'name' => 'Xorijiy mamlakatlarda malaka oshirish, stajirovka',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 12
            ],
            [
                'name' => 'O\'zbekiston Fanlar Akademiyasi akademigi ilmiy unvoniga ega bo\'lish',
                'description' => 'Hozirgi holat',
                'requires_period' => false,
                'max_points' => 100,
                'order' => 13
            ],
            [
                'name' => 'Doktorlik dissertatsiyasini himoya qilish',
                'description' => 'Oxirgi 3 yil ichida',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 14
            ],
            [
                'name' => 'Uzoq muddatli xorijiy malaka oshirish',
                'description' => 'Oxirgi 3 yilda',
                'requires_period' => true,
                'max_points' => 100,
                'order' => 15
            ]
        ];

        foreach ($categories as $category) {
            PortfolioCategory::create($category);
        }
    }
}
