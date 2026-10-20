<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class ConsultantSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('slug', 'software-saas-ai')->firstOrFail();
        $records = [
            [
                'slug' => 'nadia-solution',
                'name' => 'Nadia · Solution Architect',
                'specialty' => 'solution',
                'description' => 'Menghubungkan kebutuhan bisnis dengan arsitektur aplikasi yang tepat.',
                'translations' => [
                    'en' => [
                        'name' => 'Nadia · Solution Architect',
                        'description' => 'Connect business needs with the right application architecture.',
                    ],
                    'ja' => [
                        'name' => 'Nadia · Solution Architect',
                        'description' => 'ビジネス要件と適切なアプリケーション設計をつなぎます。',
                    ],
                ],
                'min_participants' => 1,
                'max_participants' => 6,
                'features' => [
                    'video_call',
                    'discovery',
                    'written_summary',
                ],
                'consultation_fee' => 0,
                'project_budget_from' => null,
            ],
            [
                'slug' => 'raka-cloud',
                'name' => 'Raka · Cloud Consultant',
                'specialty' => 'cloud',
                'description' => 'Membantu merancang infrastruktur, deployment, dan operasional cloud.',
                'translations' => [
                    'en' => [
                        'name' => 'Raka · Cloud Consultant',
                        'description' => 'Plan cloud infrastructure, deployment, and operations.',
                    ],
                    'ja' => [
                        'name' => 'Raka · Cloud Consultant',
                        'description' => 'クラウド基盤、デプロイ、運用を一緒に設計します。',
                    ],
                ],
                'min_participants' => 1,
                'max_participants' => 6,
                'features' => [
                    'video_call',
                    'discovery',
                    'written_summary',
                ],
                'consultation_fee' => 0,
                'project_budget_from' => null,
            ],
            [
                'slug' => 'maya-ai',
                'name' => 'Maya · AI Consultant',
                'specialty' => 'ai',
                'description' => 'Memetakan peluang AI dan otomasi dengan pengawasan manusia.',
                'translations' => [
                    'en' => [
                        'name' => 'Maya · AI Consultant',
                        'description' => 'Map AI and automation opportunities with human oversight.',
                    ],
                    'ja' => [
                        'name' => 'Maya · AI Consultant',
                        'description' => '人間の監督のもとでAIと自動化の活用機会を整理します。',
                    ],
                ],
                'min_participants' => 1,
                'max_participants' => 6,
                'features' => [
                    'video_call',
                    'discovery',
                    'written_summary',
                ],
                'consultation_fee' => 0,
                'project_budget_from' => null,
            ],
            [
                'slug' => 'bima-security',
                'name' => 'Bima · Security Consultant',
                'specialty' => 'security',
                'description' => 'Menentukan cakupan peninjauan keamanan dan prioritas mitigasi.',
                'translations' => [
                    'en' => [
                        'name' => 'Bima · Security Consultant',
                        'description' => 'Define security review scope and mitigation priorities.',
                    ],
                    'ja' => [
                        'name' => 'Bima · Security Consultant',
                        'description' => 'セキュリティレビューの範囲と対策の優先順位を定めます。',
                    ],
                ],
                'min_participants' => 1,
                'max_participants' => 6,
                'features' => [
                    'video_call',
                    'discovery',
                    'written_summary',
                ],
                'consultation_fee' => 0,
                'project_budget_from' => null,
            ],
        ];

        foreach ($records as $index => $record) {
            $company->consultants()->updateOrCreate(['slug' => $record['slug']], [...$record, 'sort_order' => $index, 'is_active' => true]);
        }
    }
}
