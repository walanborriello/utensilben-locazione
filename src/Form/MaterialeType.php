<?php

namespace App\Form;

use App\Entity\Categoria;
use App\Entity\Materiale;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaterialeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codice', TextType::class, [
                'label' => 'Codice articolo',
            ])
            ->add('nome', TextType::class, [
                'label' => 'Nome',
            ])
            ->add('descrizione', TextareaType::class, [
                'label' => 'Descrizione',
                'required' => false,
            ])
            ->add('categoria', EntityType::class, [
                'label' => 'Categoria',
                'class' => Categoria::class,
                'choice_label' => static fn (Categoria $categoria): string => (string) $categoria,
                'required' => false,
                'placeholder' => 'Nessuna categoria',
            ])
            ->add('attivo', CheckboxType::class, [
                'label' => 'Attivo',
                'required' => false,
            ])
            ->add('posizionamenti', CollectionType::class, [
                'label' => false,
                'entry_type' => PosizionamentoType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Materiale::class,
        ]);
    }
}
