<?php

namespace App\Form;

use App\Entity\Fila;
use App\Enum\TipoFila;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codice', TextType::class, [
                'label' => 'Codice',
                'help' => 'Es. "F1". Deve essere univoco.',
            ])
            ->add('nome', TextType::class, [
                'label' => 'Nome',
            ])
            ->add('tipo', EnumType::class, [
                'label' => 'Tipo di fila',
                'class' => TipoFila::class,
                'choice_label' => static fn (TipoFila $tipo): string => $tipo->label(),
                'help' => 'Cambiando il tipo, al salvataggio vengono create automaticamente le aree mancanti (non vengono mai rimosse quelle esistenti).',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fila::class,
        ]);
    }
}
