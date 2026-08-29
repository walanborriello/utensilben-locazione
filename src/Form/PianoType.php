<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\Piano;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PianoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('area', EntityType::class, [
                'label' => 'Area (fila + lato)',
                'class' => Area::class,
                'choice_label' => static fn (Area $area): string => (string) $area,
                'query_builder' => static fn (EntityRepository $er) => $er->createQueryBuilder('a')
                    ->join('a.fila', 'f')
                    ->orderBy('f.codice', 'ASC')
                    ->addOrderBy('a.lato', 'ASC'),
                'help' => 'Le aree sono generate automaticamente dalla Fila: scegli qui a quale area appartiene il piano.',
            ])
            ->add('numero', IntegerType::class, [
                'label' => 'Numero piano',
            ])
            ->add('etichetta', TextType::class, [
                'label' => 'Etichetta',
                'required' => false,
                'help' => 'Facoltativa, es. "Piano terra".',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Piano::class,
        ]);
    }
}
