<?php

namespace App\Form;

use App\Entity\Fila;
use App\Entity\Piano;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('piano', EntityType::class, [
                'label' => 'Piano',
                'class' => Piano::class,
                'choice_label' => static fn (Piano $piano): string => (string) $piano,
            ])
            ->add('lettera', ChoiceType::class, [
                'label' => 'Lettera',
                'choices' => array_combine(Fila::LETTERE_VALIDE, Fila::LETTERE_VALIDE),
            ])
            ->add('primaFila', CheckboxType::class, [
                'label' => 'È la prima fila',
                'required' => false,
                'help' => 'Un solo lato accessibile (muro dietro).',
            ])
            ->add('ultimaFila', CheckboxType::class, [
                'label' => 'È l\'ultima fila',
                'required' => false,
                'help' => 'Il corridoio finisce qui: a differenza delle altre file ha anche la sezione "d".',
            ])
            ->add('sezioniTesto', TextType::class, [
                'label' => 'Sezioni',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'a, b, c, e, f, g — aggiungi ", LETTERA" per aggiungere una nuova sezione',
                ],
                'help' => 'Lettere separate da virgola. Togli una lettera per eliminare quella sezione (non si può se ha già materiali posizionati).',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fila::class,
        ]);
    }
}
