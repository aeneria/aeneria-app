<?php

namespace App\Tests\Form\Type;

use App\Form\UpdateAccountType;
use App\Tests\AppTypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

class UpdateAccountTypeTest extends AppTypeTestCase
{
    protected function getExtensions()
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData()
    {
        $formData = [
            'username' => 'testname@example.com',
            'old_password' => 'pouet',
            'new_password' => 'toto',
            'new_password2' => 'toto',
        ];

        $objectToCompare = $this->createUser();

        $form = $this->factory->create(UpdateAccountType::class, $objectToCompare, ['data_class' => null]);

        // submit the data to the form directly
        $form->submit($formData);
        $dataFromForm = $form->getData();

        self::assertTrue($form->isSynchronized());

        // check that $objectToCompare was modified as expected when the form was submitted
        self::assertEquals($formData['username'], $dataFromForm['username']);
        self::assertEquals($formData['old_password'], $dataFromForm['old_password']);
        self::assertEquals($formData['new_password'], $dataFromForm['new_password']);
        self::assertEquals($formData['new_password2'], $dataFromForm['new_password2']);
        self::assertEquals($objectToCompare, $dataFromForm['user']);

        $view = $form->createView();
        $children = $view->children;

        foreach (\array_keys($formData) as $key) {
            if ('user' !== $key) {
                $this->assertArrayHasKey($key, $children);
            }
        }
    }

    public function testSubmitWrongUsernameData()
    {
        $formData = [
            'username' => 'testname',
            'old_password' => 'pouet',
            'new_password' => 'toto',
            'new_password2' => 'toto',
        ];

        $objectToCompare = $this->createUser();

        $form = $this->factory->create(UpdateAccountType::class, $objectToCompare, ['data_class' => null]);

        // submit the data to the form directly
        $form->submit($formData);

        self::assertFalse($form->isValid());
        self::assertCount(1, $form->get('username')->getErrors());
    }
}
