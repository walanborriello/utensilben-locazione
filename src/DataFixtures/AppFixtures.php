<?php

namespace App\DataFixtures;

use App\Entity\Categoria;
use App\Entity\Fila;
use App\Entity\Materiale;
use App\Entity\Piano;
use App\Entity\Posizionamento;
use App\Entity\Ripiano;
use App\Service\FilaSezioneSynchronizer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Dati di prova plausibili per una ferramenta: il piano Terra con le file
 * A-L (sezioni e ripiani generati da FilaSezioneSynchronizer, esattamente
 * come farebbe l'admin da form), il piano Soppalco ancora vuoto, alcune
 * categorie e una dozzina di materiali — incluso uno con due posizioni e uno
 * senza nessuna — per collaudare tutti i casi previsti dalla ricerca.
 */
class AppFixtures extends Fixture
{
    public function __construct(private readonly FilaSezioneSynchronizer $filaSezioneSynchronizer)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $categorie = $this->creaCategorie($manager);

        $terra = new Piano();
        $terra->setNome('Terra');
        $manager->persist($terra);

        $soppalco = new Piano();
        $soppalco->setNome('Soppalco');
        $manager->persist($soppalco);

        $file = [];
        foreach (Fila::LETTERE_VALIDE as $lettera) {
            $fila = new Fila();
            $fila->setLettera($lettera);
            $fila->setPrimaFila('A' === $lettera);
            $fila->setUltimaFila('L' === $lettera);
            $terra->addFila($fila);
            $this->filaSezioneSynchronizer->sincronizza($fila);
            $manager->persist($fila);
            $file[$lettera] = $fila;
        }

        $ripiano = static function (Fila $fila, string $sezioneLettera, int $numero): Ripiano {
            foreach ($fila->getSezioni() as $sezione) {
                if ($sezioneLettera === $sezione->getLettera()) {
                    foreach ($sezione->getRipiani() as $r) {
                        if ($numero === $r->getNumero()) {
                            return $r;
                        }
                    }
                }
            }

            throw new \RuntimeException('Ripiano non trovato nelle fixture.');
        };

        $materiali = [
            ['codice' => 'VT-001', 'nome' => 'Viti autofilettanti 4x30 (conf. 100pz)', 'cat' => 'Viteria e minuteria', 'um' => 'conf', 'pos' => [[$ripiano($file['A'], 'a', 1), true]]],
            ['codice' => 'VT-002', 'nome' => 'Chiodi in acciaio 2x40 (conf. 1kg)', 'cat' => 'Viteria e minuteria', 'um' => 'kg', 'pos' => [[$ripiano($file['A'], 'a', 1), true]]],
            ['codice' => 'VT-010', 'nome' => 'Tasselli ad espansione 8mm (conf. 50pz)', 'cat' => 'Viteria e minuteria', 'um' => 'conf', 'pos' => [[$ripiano($file['A'], 'a', 2), true]]],
            ['codice' => 'UT-101', 'nome' => 'Martello da carpentiere 500g', 'cat' => 'Utensili manuali', 'um' => 'pz', 'pos' => [[$ripiano($file['B'], 'a', 1), true]]],
            ['codice' => 'UT-102', 'nome' => 'Cacciaviti a taglio/croce (set 6pz)', 'cat' => 'Utensili manuali', 'um' => 'set', 'pos' => [[$ripiano($file['B'], 'a', 2), true]]],
            ['codice' => 'UT-150', 'nome' => 'Chiave inglese regolabile 250mm', 'cat' => 'Utensili manuali', 'um' => 'pz',
                // Materiale con due posizioni (principale in B, seconda scorta in C), per collaudare anche questo caso.
                'pos' => [[$ripiano($file['B'], 'b', 3), true], [$ripiano($file['C'], 'a', 1), false]]],
            ['codice' => 'UT-160', 'nome' => 'Nastro isolante nero 19mm x 20m', 'cat' => 'Utensili manuali', 'um' => 'pz', 'pos' => [[$ripiano($file['C'], 'a', 2), true]]],
            ['codice' => 'EL-201', 'nome' => 'Trapano avvitatore a batteria 18V', 'cat' => 'Elettroutensili', 'um' => 'pz', 'pos' => [[$ripiano($file['D'], 'a', 1), true]]],
            ['codice' => 'EL-210', 'nome' => 'Smerigliatrice angolare 125mm', 'cat' => 'Elettroutensili', 'um' => 'pz', 'pos' => [[$ripiano($file['D'], 'a', 2), true]]],
            ['codice' => 'DPI-301', 'nome' => 'Guanti da lavoro in pelle taglia L', 'cat' => 'Dispositivi di protezione', 'um' => 'paio', 'pos' => [[$ripiano($file['E'], 'a', 1), true]]],
            ['codice' => 'DPI-305', 'nome' => 'Occhiali di protezione antigraffio', 'cat' => 'Dispositivi di protezione', 'um' => 'pz', 'pos' => [[$ripiano($file['E'], 'a', 2), true]]],
            // In fila L (ultima) esiste anche la sezione "d": qui il corridoio finisce.
            ['codice' => 'EL-301', 'nome' => 'Scala portatile in alluminio 3 gradini', 'cat' => 'Elettroutensili', 'um' => 'pz', 'pos' => [[$ripiano($file['L'], 'd', 1), true]]],
            // Un materiale volutamente senza posizione, per mostrare il filtro "solo senza posizione".
            ['codice' => 'NEW-900', 'nome' => 'Sega a batteria multifunzione (nuovo arrivo)', 'cat' => 'Elettroutensili', 'um' => 'pz', 'pos' => []],
        ];

        foreach ($materiali as $dati) {
            $materiale = new Materiale();
            $materiale->setCodice($dati['codice']);
            $materiale->setNome($dati['nome']);
            $materiale->setCategoria($categorie[$dati['cat']]);
            $materiale->setAttivo(true);

            foreach ($dati['pos'] as $posizioneDati) {
                [$ripianoEntity, $principale] = $posizioneDati;
                $posizionamento = new Posizionamento();
                $posizionamento->setRipiano($ripianoEntity);
                $posizionamento->setPrincipale($principale);
                $materiale->addPosizionamento($posizionamento);
            }

            $manager->persist($materiale);
        }

        $manager->flush();
    }

    /**
     * @return array<string, Categoria>
     */
    private function creaCategorie(ObjectManager $manager): array
    {
        $nomi = ['Viteria e minuteria', 'Utensili manuali', 'Elettroutensili', 'Dispositivi di protezione'];
        $categorie = [];

        foreach ($nomi as $nome) {
            $categoria = new Categoria();
            $categoria->setNome($nome);
            $manager->persist($categoria);
            $categorie[$nome] = $categoria;
        }

        return $categorie;
    }
}
