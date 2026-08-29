<?php

namespace App\DataFixtures;

use App\Entity\Area;
use App\Entity\Categoria;
use App\Entity\Fila;
use App\Entity\Materiale;
use App\Entity\Piano;
use App\Entity\Posizionamento;
use App\Enum\LatoArea;
use App\Enum\TipoFila;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Dati di prova plausibili per una ferramenta: 3 file (una unica, due
 * divise), qualche piano per area, alcune categorie e una decina di
 * materiali, incluso un materiale con due posizioni, per collaudare tutti i
 * casi previsti dalla ricerca.
 */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categorie = $this->creaCategorie($manager);

        $fila1 = $this->creaFila($manager, 'F1', 'Fila 1 - Minuteria', TipoFila::UNICA, 4);
        $fila2 = $this->creaFila($manager, 'F2', 'Fila 2 - Utensili manuali', TipoFila::DIVISA, 4);
        $fila3 = $this->creaFila($manager, 'F3', 'Fila 3 - Elettroutensili e DPI', TipoFila::DIVISA, 3);

        $piano = static function (Fila $fila, LatoArea $lato, int $numero) use ($manager): Piano {
            foreach ($fila->getAree() as $area) {
                if ($lato === $area->getLato()) {
                    foreach ($area->getPiani() as $p) {
                        if ($numero === $p->getNumero()) {
                            return $p;
                        }
                    }
                }
            }
            throw new \RuntimeException('Piano non trovato nelle fixture.');
        };

        $materiali = [
            ['codice' => 'VT-001', 'nome' => 'Viti autofilettanti 4x30 (conf. 100pz)', 'cat' => 'Viteria e minuteria', 'um' => 'conf', 'pos' => [[$piano($fila1, LatoArea::UNICA, 1), true]]],
            ['codice' => 'VT-002', 'nome' => 'Chiodi in acciaio 2x40 (conf. 1kg)', 'cat' => 'Viteria e minuteria', 'um' => 'kg', 'pos' => [[$piano($fila1, LatoArea::UNICA, 1), true]]],
            ['codice' => 'VT-010', 'nome' => 'Tasselli ad espansione 8mm (conf. 50pz)', 'cat' => 'Viteria e minuteria', 'um' => 'conf', 'pos' => [[$piano($fila1, LatoArea::UNICA, 2), true]]],
            ['codice' => 'UT-101', 'nome' => 'Martello da carpentiere 500g', 'cat' => 'Utensili manuali', 'um' => 'pz', 'pos' => [[$piano($fila2, LatoArea::SINISTRA, 1), true]]],
            ['codice' => 'UT-102', 'nome' => 'Cacciaviti a taglio/croce (set 6pz)', 'cat' => 'Utensili manuali', 'um' => 'set', 'pos' => [[$piano($fila2, LatoArea::SINISTRA, 2), true]]],
            ['codice' => 'UT-150', 'nome' => 'Chiave inglese regolabile 250mm', 'cat' => 'Utensili manuali', 'um' => 'pz',
                // Materiale con due posizioni (principale a sinistra, seconda scorta a destra) e sezione valorizzata, per mostrare anche questo caso.
                'pos' => [[$piano($fila2, LatoArea::SINISTRA, 3), true, 'C'], [$piano($fila2, LatoArea::DESTRA, 1), false, 'A']]],
            ['codice' => 'UT-160', 'nome' => 'Nastro isolante nero 19mm x 20m', 'cat' => 'Utensili manuali', 'um' => 'pz', 'pos' => [[$piano($fila2, LatoArea::DESTRA, 2), true]]],
            ['codice' => 'EL-201', 'nome' => 'Trapano avvitatore a batteria 18V', 'cat' => 'Elettroutensili', 'um' => 'pz', 'pos' => [[$piano($fila3, LatoArea::SINISTRA, 1), true]]],
            ['codice' => 'EL-210', 'nome' => 'Smerigliatrice angolare 125mm', 'cat' => 'Elettroutensili', 'um' => 'pz', 'pos' => [[$piano($fila3, LatoArea::SINISTRA, 2), true]]],
            ['codice' => 'DPI-301', 'nome' => 'Guanti da lavoro in pelle taglia L', 'cat' => 'Dispositivi di protezione', 'um' => 'paio', 'pos' => [[$piano($fila3, LatoArea::DESTRA, 1), true]]],
            ['codice' => 'DPI-305', 'nome' => 'Occhiali di protezione antigraffio', 'cat' => 'Dispositivi di protezione', 'um' => 'pz', 'pos' => [[$piano($fila3, LatoArea::DESTRA, 2), true]]],
            // Un materiale volutamente senza posizione, per mostrare il filtro "solo senza posizione".
            ['codice' => 'NEW-900', 'nome' => 'Sega a batteria multifunzione (nuovo arrivo)', 'cat' => 'Elettroutensili', 'um' => 'pz', 'pos' => []],
        ];

        foreach ($materiali as $dati) {
            $materiale = new Materiale();
            $materiale->setCodice($dati['codice']);
            $materiale->setNome($dati['nome']);
            $materiale->setCategoria($categorie[$dati['cat']]);
            $materiale->setUnitaMisura($dati['um']);
            $materiale->setAttivo(true);

            foreach ($dati['pos'] as $posizioneDati) {
                [$pianoEntity, $principale] = $posizioneDati;
                $posizionamento = new Posizionamento();
                $posizionamento->setPiano($pianoEntity);
                $posizionamento->setPrincipale($principale);
                $posizionamento->setSezione($posizioneDati[2] ?? null);
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

    private function creaFila(ObjectManager $manager, string $codice, string $nome, TipoFila $tipo, int $numeroPiani): Fila
    {
        $fila = new Fila();
        $fila->setCodice($codice);
        $fila->setNome($nome);
        $fila->setTipo($tipo);

        $lati = TipoFila::DIVISA === $tipo ? [LatoArea::SINISTRA, LatoArea::DESTRA] : [LatoArea::UNICA];

        foreach ($lati as $lato) {
            $area = new Area();
            $area->setLato($lato);

            for ($n = 1; $n <= $numeroPiani; ++$n) {
                $piano = new Piano();
                $piano->setNumero($n);
                $area->addPiano($piano);
            }

            $fila->addArea($area);
        }

        $manager->persist($fila);

        return $fila;
    }
}
