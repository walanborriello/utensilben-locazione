<?php

namespace App\Form;

use App\Entity\Piano;
use App\Entity\Posizionamento;
use App\Repository\PianoRepository;
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
 */
class PosizionamentoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('piano', EntityType::class, [
                'label' => 'Piano',
                'class' => Piano::class,
                'choice_label' => static fn (Piano $piano): string => (string) $piano,
                'query_builder' => static fn (PianoRepository $pianoRepository) => $pianoRepository
                    ->createQueryBuilder('p')
                    ->join('p.area', 'a')->addSelect('a')
                    ->join('a.fila', 'f')->addSelect('f')
                    ->orderBy('f.codice', 'ASC')
                    ->addOrderBy('a.lato', 'ASC')
                    ->addOrderBy('p.numero', 'ASC'),
            ])
            ->add('principale', CheckboxType::class, [
                'label' => 'Posizione principale',
                'required' => false,
            ])
            ->add('sezione', TextType::class, [
                'label' => 'Sezione',
                'required' => false,
                'help' => 'Facoltativa: punto del piano in cui si trova il materiale, es. da "A" a "Z" da un estremo all\'altro.',
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
