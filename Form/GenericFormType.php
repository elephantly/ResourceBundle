<?php

namespace Elephantly\ResourceBundle\Form;

use AppBundle\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

/**
* primary @author purplebabar(lalung.alexandre@gmail.com)
*/
class GenericFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $class = $options['data_class'];
        $reflectionExtractor = new ReflectionExtractor();

        $properties = $reflectionExtractor->getProperties($class);

        foreach ($properties as $property) {
            $builder->add($property);
        }

    }
}
