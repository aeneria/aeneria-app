<?php

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\UserType;
use App\Tests\AppTypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Validator\Validation;

class UserTypeTest extends AppTypeTestCase
{
    private $passwordEncoder;

    protected function setUp(): void
    {
        // mock any dependencies
        $this->passwordEncoder = $this->createMock(UserPasswordEncoder::class);

        parent::setUp();
    }

    protected function getExtensions()
    {
        // create a type instance with the mocked dependencies
        $type = new UserType($this->passwordEncoder);
        $validator = Validation::createValidator();

        return [
            // register the type instances with the PreloadedExtension
            new PreloadedExtension([$type], []),
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData()
    {
        $formData = [
            'username' => 'testname@example.com',
            'is_admin' => true,
            'is_active' => false,
        ];
        $object = $this->createUser([
            'username' => 'testname@example.com',
            'roles' => [User::ROLE_ADMIN],
            'active' => false,
        ]);

        $objectToCompare = $this->createUser();

        $form = $this->factory->create(UserType::class, null, ['data_class' => null]);

        // submit the data to the form directly
        $form->submit($formData);
        $objectToCompare = $form->getData();

        self::assertTrue($form->isSynchronized());

        // check that $objectToCompare was modified as expected when the form was submitted
        self::assertEquals($object->getUsername(), $objectToCompare->getUsername());
        self::assertEquals($object->isAdmin(), $objectToCompare->isAdmin());
        self::assertEquals($object->isActive(), $objectToCompare->isActive());

        $view = $form->createView();
        $children = $view->children;

        foreach (\array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function testSubmitWrongUsernameData()
    {
        $formData = [
            'username' => 'testname',
            'is_admin' => true,
            'is_active' => false,
        ];

        $form = $this->factory->create(UserType::class, null, ['data_class' => null]);

        // submit the data to the form directly
        $form->submit($formData);

        self::assertFalse($form->isValid());
        self::assertCount(1, $form->get('username')->getErrors());
    }
}
