<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Devis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class DevisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // --- Informations client (champs "à plat", non mappés sur Devis) ---
            ->add('clientNom', TextType::class, [
                'label' => 'Nom',
                'mapped' => false,
                'constraints' => [new Assert\NotBlank(message: 'Le nom est obligatoire.')],
            ])
            ->add('clientPrenom', TextType::class, [
                'label' => 'Prénom',
                'mapped' => false,
                'constraints' => [new Assert\NotBlank(message: 'Le prénom est obligatoire.')],
            ])
            ->add('clientEmail', EmailType::class, [
                'label' => 'Email',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(message: 'L\'email est obligatoire.'),
                    new Assert\Email(message: 'L\'email n\'est pas valide.'),
                ],
            ])
            ->add('clientTelephone', TelType::class, [
                'label' => 'Téléphone',
                'mapped' => false,
                'constraints' => [new Assert\NotBlank(message: 'Le téléphone est obligatoire.')],
            ])
            ->add('clientAdresse', TextType::class, [
                'label' => 'Adresse',
                'mapped' => false,
                'constraints' => [new Assert\NotBlank(message: 'L\'adresse est obligatoire.')],
            ])
            ->add('clientCodePostal', TextType::class, [
                'label' => 'Code postal',
                'mapped' => false,
                'constraints' => [new Assert\NotBlank(message: 'Le code postal est obligatoire.')],
            ])
            ->add('clientVille', TextType::class, [
                'label' => 'Ville',
                'mapped' => false,
                'constraints' => [new Assert\NotBlank(message: 'La ville est obligatoire.')],
            ])
            // --- Informations devis ---
            ->add('typeInstallation', ChoiceType::class, [
                'label' => 'Type d\'installation',
                'choices' => [
                    'Résidentiel' => 'residentiel',
                    'Agricole' => 'agricole',
                    'Industriel / Tertiaire' => 'industriel',
                ],
                'placeholder' => 'Sélectionnez un type',
                'constraints' => [new Assert\NotBlank(message: 'Veuillez sélectionner un type d\'installation.')],
            ])
            ->add('nombrePanneaux', IntegerType::class, [
                'label' => 'Nombre de panneaux (approximatif)',
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez indiquer un nombre de panneaux.'),
                    new Assert\Positive(message: 'Le nombre de panneaux doit être positif.'),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Informations complémentaires',
                'required' => false,
                'attr' => ['rows' => 4, 'placeholder' => 'Accès au toit, date souhaitée, contraintes particulières...'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Devis::class,
        ]);
    }
}
