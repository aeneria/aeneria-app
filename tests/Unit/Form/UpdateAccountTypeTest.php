<?php

namespace App\Tests\Form\Type;

use App\Form\UpdateAccountType;
use App\Tests\AppTypeTestCase;

class UpdateAccountTypeTest extends AppTypeTestCase
{
    public function testSubmitValidData()
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
        $dataFromForm = $form->getData();

        $this->assertTrue($form->isSynchronized());

        // check that $objectToCompare was modified as expected when the form was submitted
        $this->assertEquals($formData['username'], $dataFromForm['username']);
        $this->assertEquals($formData['old_password'], $dataFromForm['old_password']);
        $this->assertEquals($formData['new_password'], $dataFromForm['new_password']);
        $this->assertEquals($formData['new_password2'], $dataFromForm['new_password2']);
        $this->assertEquals($objectToCompare, $dataFromForm['user']);

        $view = $form->createView();
        $children = $view->children;

        foreach (\array_keys($formData) as $key) {
            if ('user' !== $key) {
                $this->assertArrayHasKey($key, $children);
            }
        }
    }
}
