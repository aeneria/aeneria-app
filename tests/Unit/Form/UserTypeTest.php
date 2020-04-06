<?php
namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\UserType;
use App\Tests\AppTypeTestCase;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

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

        return [
            // register the type instances with the PreloadedExtension
            new PreloadedExtension([$type], []),
        ];
    }

    public function testSubmitValidData()
    {
        $formData = [
            'username' => 'testname',
            'is_admin' => true,
            'is_active' => false,
        ];
        $object = $this->createUser([
            'username' => 'testname',
            'roles' => [User::ROLE_ADMIN],
            'active' => false,
        ]);

        $objectToCompare = $this->createUser();

        $form = $this->factory->create(UserType::class, null,['data_class' => null]);

        // submit the data to the form directly
        $form->submit($formData);
        $objectToCompare = $form->getData();

        $this->assertTrue($form->isSynchronized());

        // check that $objectToCompare was modified as expected when the form was submitted
        $this->assertEquals($object->getUsername(), $objectToCompare->getUsername());
        $this->assertEquals($object->isAdmin(), $objectToCompare->isAdmin());
        $this->assertEquals($object->isActive(), $objectToCompare->isActive());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
