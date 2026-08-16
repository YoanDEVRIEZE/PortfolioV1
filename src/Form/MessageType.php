<?php

namespace App\Form;

use App\Entity\Message;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname', TextType::class, [
                'attr' => [
                    'class' => 'formulaireinput',
                    'placeholder' => 'Nom',
                    'autocomplete' => 'family-name',
                    'maxlength' => 100,
                ],
                'label' => false,
                'required' => true,
            ])
            ->add('firstname', TextType::class, [
                'attr' => [
                    'class' => 'formulaireinput',
                    'placeholder' => 'Prénom',
                    'autocomplete' => 'given-name',
                    'maxlength' => 100,
                ],
                'label' => false,
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'formulaireinput',
                    'placeholder' => 'E-mail',
                    'autocomplete' => 'email',
                    'maxlength' => 255,
                ],
                'label' => false,
                'required' => true,
            ])
            ->add('message', TextareaType::class, [
                'attr' => [
                    'class' => 'formulairearea',
                    'placeholder' => 'Votre message',
                    'maxlength' => 2000,
                ],
                'label' => false,
                'required' => true,
            ])
            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'portfolio_accueil',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'portfolio_contact',
        ]);
    }
}
