<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $projects = [
            [
                'name' => 'Lançamento do aplicativo',
                'description' => 'Planejamento e execução da primeira versão pública do produto.',
                'status' => ProjectStatus::Active,
                'tasks' => [
                    [
                        'title' => 'Definir escopo do MVP',
                        'description' => 'Validar as funcionalidades essenciais com as partes interessadas.',
                        'status' => TaskStatus::Done,
                        'priority' => TaskPriority::High,
                        'due_date' => today()->subDays(10),
                    ],
                    [
                        'title' => 'Preparar ambiente de homologação',
                        'description' => 'Disponibilizar a aplicação para os testes internos.',
                        'status' => TaskStatus::InProgress,
                        'priority' => TaskPriority::High,
                        'due_date' => today()->subDays(2),
                    ],
                    [
                        'title' => 'Revisar conteúdo da página inicial',
                        'description' => null,
                        'status' => TaskStatus::Todo,
                        'priority' => TaskPriority::Medium,
                        'due_date' => today()->addDays(5),
                    ],
                ],
            ],
            [
                'name' => 'Melhorias de experiência',
                'description' => 'Aprimoramentos de usabilidade identificados após entrevistas com usuários.',
                'status' => ProjectStatus::Active,
                'tasks' => [
                    [
                        'title' => 'Mapear jornada do usuário',
                        'description' => 'Documentar os principais fluxos e pontos de atrito.',
                        'status' => TaskStatus::InProgress,
                        'priority' => TaskPriority::Medium,
                        'due_date' => today()->addDays(3),
                    ],
                    [
                        'title' => 'Criar protótipo responsivo',
                        'description' => 'Preparar telas para dispositivos móveis e desktop.',
                        'status' => TaskStatus::Todo,
                        'priority' => TaskPriority::High,
                        'due_date' => today()->addDays(8),
                    ],
                    [
                        'title' => 'Conduzir teste de usabilidade',
                        'description' => null,
                        'status' => TaskStatus::Todo,
                        'priority' => TaskPriority::Low,
                        'due_date' => null,
                    ],
                ],
            ],
            [
                'name' => 'Migração do portal legado',
                'description' => 'Projeto concluído de migração dos conteúdos do portal anterior.',
                'status' => ProjectStatus::Archived,
                'tasks' => [
                    [
                        'title' => 'Inventariar conteúdos',
                        'description' => 'Catalogar páginas e arquivos que precisam ser preservados.',
                        'status' => TaskStatus::Done,
                        'priority' => TaskPriority::Medium,
                        'due_date' => today()->subDays(30),
                    ],
                    [
                        'title' => 'Validar redirecionamentos',
                        'description' => 'Confirmar que os endereços antigos apontam para as novas páginas.',
                        'status' => TaskStatus::Done,
                        'priority' => TaskPriority::High,
                        'due_date' => today()->subDays(20),
                    ],
                ],
            ],
        ];

        foreach ($projects as $projectData) {
            $tasks = $projectData['tasks'];
            unset($projectData['tasks']);

            $project = Project::query()->updateOrCreate(
                ['name' => $projectData['name']],
                $projectData,
            );

            $project->tasks()->withTrashed()->forceDelete();
            $project->tasks()->createMany($tasks);
        }
    }
}
