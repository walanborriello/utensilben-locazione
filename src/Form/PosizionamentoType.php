<?php

namespace App\Form;

use App\Entity\Fila;
use App\Entity\Piano;
use App\Entity\Posizionamento;
use App\Entity\Ripiano;
use App\Entity\Sezione;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Una singola riga "posizione sullo scaffale". Non ha piu' un campo
 * "materiale": e' incorporata direttamente nel form del prodotto
 * (MaterialeType), il materiale e' quindi implicito.
 *
 * "piano", "fila" e "sezione" sono select di sola interfaccia ('mapped' =>
 * false): servono solo a restringere a cascata le opzioni di "ripiano",
 * l'unico campo che viene davvero salvato sull'entita'. Il filtraggio a
 * cascata vero e proprio (JS) legge gli attributi data-piano-id/data-fila-id/
 * data-sezione-id impostati qui sotto via choice_attr — vedi assets/app.js.
 */
class PosizionamentoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('piano', EntityType::class, [
                'label' => 'Piano',
                'class' => Piano::class,
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Seleziona...',
                'choice_label' => static fn (Piano $piano): string => (string) $piano,
            ])
            ->add('fila', EntityType::class, [
                'label' => 'Fila',
                'class' => Fila::class,
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Seleziona...',
                'choice_label' => static fn (Fila $fila): string => $fila->getLettera(),
                'choice_attr' => static fn (Fila $fila): array => [
                    'data-piano-id' => $fila->getPiano()?->getId(),
                ],
                'query_builder' => static fn (EntityRepository $er) => $er->createQueryBuilder('f')
                    ->join('f.piano', 'p')->addSelect('p')
                    ->orderBy('p.nome', 'ASC')
                    ->addOrderBy('f.lettera', 'ASC'),
            ])
            ->add('sezione', EntityType::class, [
                'label' => 'Sezione',
                'class' => Sezione::class,
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Seleziona...',
                'choice_label' => static fn (Sezione $sezione): string => $sezione->getLettera(),
                'choice_attr' => static fn (Sezione $sezione): array => [
                    'data-fila-id' => $sezione->getFila()?->getId(),
                    'data-piano-id' => $sezione->getFila()?->getPiano()?->getId(),
                ],
                'query_builder' => static fn (EntityRepository $er) => $er->createQueryBuilder('s')
                    ->join('s.fila', 'f')->addSelect('f')
                    ->join('f.piano', 'p')->addSelect('p')
                    ->orderBy('p.nome', 'ASC')
                    ->addOrderBy('f.lettera', 'ASC')
                    ->addOrderBy('s.lettera', 'ASC'),
            ])
            ->add('ripiano', EntityType::class, [
                'label' => 'Ripiano',
                'class' => Ripiano::class,
                'placeholder' => 'Seleziona...',
                'choice_label' => static fn (Ripiano $ripiano): string => (string) $ripiano->getNumero(),
                'choice_attr' => static fn (Ripiano $ripiano): array => [
                    'data-sezione-id' => $ripiano->getSezione()?->getId(),
                    'data-fila-id' => $ripiano->getSezione()?->getFila()?->getId(),
                    'data-piano-id' => $ripiano->getSezione()?->getFila()?->getPiano()?->getId(),
                ],
                'query_builder' => static fn (EntityRepository $er) => $er->createQueryBuilder('r')
                    ->join('r.sezione', 's')->addSelect('s')
                    ->join('s.fila', 'f')->addSelect('f')
                    ->join('f.piano', 'p')->addSelect('p')
                    ->orderBy('p.nome', 'ASC')
                    ->addOrderBy('f.lettera', 'ASC')
                    ->addOrderBy('s.lettera', 'ASC')
                    ->addOrderBy('r.numero', 'ASC'),
            ])
            ->add('principale', CheckboxType::class, [
                'label' => 'Posizione principale',
                'required' => false,
            ])
            ->add('note', TextType::class, [
                'label' => 'Note',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Posizionamento::class,
        ]);
    }
}
