<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PhaseTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Template Essencial: 9 fases que cobrem a maioria dos cenários de obras.
     */
    public function run(): void
    {
        $templates = $this->getEssentialTemplate();

        // Este seeder fornece templates que podem ser usados por aplicações
        // para criar fases padrão em projetos. Pode ser estendido para
        // armazenar templates em uma tabela dedicada no futuro.

        // Por enquanto, apenas exibe os templates disponíveis
        $this->command->info('Phase Templates disponíveis:');
        $this->command->info('');
        $this->command->info('Template Essencial (9 fases):');
        
        foreach ($templates as $index => $phase) {
            $this->command->info(($index + 1) . '. ' . $phase['name']);
            $this->command->info('   ' . $phase['description']);
        }
        
        $this->command->info('');
        $this->command->info('Use estes templates ao criar projetos via API ou interface.');
    }

    /**
     * Get the essential phase template (9 phases).
     *
     * @return array<int, array<string, string>>
     */
    protected function getEssentialTemplate(): array
    {
        return [
            [
                'name' => 'Planejamento e Projeto',
                'description' => 'Briefing, levantamento, estudo preliminar, projeto legal, projeto executivo, compatibilização, orçamento e cronograma.',
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Preparação do Canteiro',
                'description' => 'Tapumes, segurança, ligações provisórias (água/energia), logística, armazenamento e proteções de áreas existentes.',
                'color' => '#8B5CF6',
            ],
            [
                'name' => 'Fundação',
                'description' => 'Sondagem, estacas, blocos, sapatas, radier, baldrames e impermeabilização da fundação.',
                'color' => '#D97706',
            ],
            [
                'name' => 'Estrutura',
                'description' => 'Fôrmas, armaduras, concretagens (lajes/pilares/vigas), escadas e poço de elevador.',
                'color' => '#DC2626',
            ],
            [
                'name' => 'Vedações e Cobertura',
                'description' => 'Alvenaria de vedação, drywall, divisórias, cobertura, estrutura, telhamento, calhas, rufos e impermeabilizações de laje.',
                'color' => '#059669',
            ],
            [
                'name' => 'Instalações',
                'description' => 'Elétrica, quadros, iluminação, hidráulica, água, esgoto, gás, HVAC, ar-condicionado, incêndio, dados, telefonia, CFTV e SPDA.',
                'color' => '#F59E0B',
            ],
            [
                'name' => 'Esquadrias e Fachada',
                'description' => 'Esquadrias (alumínio/PVC/madeira), vidros, revestimento de fachada e pintura externa.',
                'color' => '#06B6D4',
            ],
            [
                'name' => 'Acabamentos Internos',
                'description' => 'Revestimentos argamassados, pisos, revestimentos de parede, forros, pintura, louças, metais e marcenaria.',
                'color' => '#EC4899',
            ],
            [
                'name' => 'Comissionamento e Entrega',
                'description' => 'Testes e start-up de instalações, limpeza final, vistorias, checklist, correções finais, AVCB, Habite-se, manual, treinamento e termo de recebimento.',
                'color' => '#10B981',
            ],
        ];
    }

    /**
     * Get advanced phase template (installations split into separate phases).
     *
     * @return array<int, array<string, string>>
     */
    protected function getAdvancedTemplate(): array
    {
        $essential = $this->getEssentialTemplate();
        
        // Remove "Instalações" (index 5) and replace with detailed phases
        array_splice($essential, 5, 1, [
            [
                'name' => 'Instalações Elétricas',
                'description' => 'Quadros, iluminação, tomadas, cabeamento elétrico e automação.',
                'color' => '#F59E0B',
            ],
            [
                'name' => 'Instalações Hidráulicas/Sanitárias',
                'description' => 'Água fria, água quente, esgoto e drenagem.',
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Gás',
                'description' => 'Instalação de tubulação e conexões de gás.',
                'color' => '#EF4444',
            ],
            [
                'name' => 'HVAC/Climatização',
                'description' => 'Ar-condicionado, ventilação e exaustão.',
                'color' => '#06B6D4',
            ],
            [
                'name' => 'Incêndio',
                'description' => 'Hidrantes, sprinklers, alarmes e sinalização.',
                'color' => '#DC2626',
            ],
            [
                'name' => 'Dados/Telefonia/CFTV/SPDA',
                'description' => 'Cabeamento estruturado, telefonia, câmeras de segurança e para-raios.',
                'color' => '#8B5CF6',
            ],
        ]);
        
        return $essential;
    }
}
