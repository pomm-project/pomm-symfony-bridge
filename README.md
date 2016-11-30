# Pomm Symfony bridge

This package contains Pomm2 profiler shared files between Silex and the Symfony2 full stack framework.

Normally it should not be installed manually since it is required by either the pomm-project/pomm-profiler-service-provider (Silex) or the pomm-project/pomm-bundle (Symfony).

# Form Type

```
public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
        ->add('category', 'flexible_entity', array(
            'model' => '\AppBundle\Model\MyDb1\PublicSchema\CategoryModel',
            'property' => 'title',
            'expanded' => false,
            'multiple' => false,
            'label'    => 'Category',
            'required' => false,
            'where'    => Where::create('type @> $*::varchar[]', [['product']]),
            'suffix'   => 'ORDER BY title'
            'group_by' => 'group'
        ));

}
```