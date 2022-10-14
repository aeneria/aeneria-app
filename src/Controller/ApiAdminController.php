<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PlaceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;

class ApiAdminController extends AbstractAppController
{
    /** @var UserRepository */
    private $userRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        bool $userCanSharePlace,
        bool $placeCanBePublic,
        bool $isDemoMode,
        PlaceRepository $placeRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($userCanSharePlace, $placeCanBePublic, $isDemoMode, $placeRepository);

        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function userList(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $data = $request->query->all();

        if (!\key_exists('limit', $data)) {
            return $this->dataValidationErrorResponse('limit', "Vous devez fournir un argument 'limit'.");
        }
        if (!\key_exists('offset', $data)) {
            return $this->dataValidationErrorResponse('limit', "Vous devez fournir un argument 'offset'.");
        }

        return new JsonResponse(\json_encode(
            $this->userRepository->list((int) $data['offset'], (int) $data['limit'])
        ), 200);
    }

    public function userCount(): JsonResponse
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        return new JsonResponse($this->userRepository->count([]), 200);
    }

    /**
     * Add new user form view
     */
    public function addUser(Request $request, UserPasswordEncoderInterface $passwordEncoder): JsonResponse
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $validator = Validation::createValidator();
        $data = \json_decode($request->getContent());

        if (!$data->email) {
            return $this->dataValidationErrorResponse('email', "Vous devez fournir un argument 'email'.");
        }
        if (\count($validator->validate($data->email, [new Email()]))) {
            return $this->dataValidationErrorResponse('email', "L'adresse email est invalide.");
        }
        if (!$data->password) {
            return $this->dataValidationErrorResponse('password', "Vous devez fournir un argument 'password'.");
        }
        if ($this->userRepository->findOneByUsername($data->email)) {
            return $this->dataValidationErrorResponse('email', "Un utilisateur existe déjà pour cette adresse email.");
        }

        $newUser = new User();

        $newUser
            ->setUsername($data->email)
            ->setPassword($passwordEncoder->encodePassword($newUser, $data->password))
            ->setActive($data->isActive ?? false)
            ->setRoles(($data->isAdmin ?? false) ? ['ROLE_ADMIN'] : [])
        ;

        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        return new JsonResponse(\json_encode($newUser), 200);
    }

    public function updateUser(
        string $id,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    ): JsonResponse {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $validator = Validation::createValidator();
        $data = \json_decode($request->getContent());

        if (!$user = $this->userRepository->find($id)) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }
        if (!($data->isActive ?? false) && $this->userRepository->isLastAdmin($user->getUsername())) {
            return $this->dataValidationErrorResponse('isActive', "Vous ne pouvez pas désactiver cet utilisateur, c'est le dernier administrateur !");
        }
        if (!$data->email) {
            return $this->dataValidationErrorResponse('email', "Vous devez fournir un argument 'email'.");
        }
        if (\count($validator->validate($data->email, [new Email()]))) {
            return $this->dataValidationErrorResponse('email', "L'adresse email est invalide.");
        }
        if ($this->userRepository->findOneByUsername($data->email)) {
            return $this->dataValidationErrorResponse('email', "Un utilisateur existe déjà pour cette adresse email.");
        }

        $user
            ->setUsername($data->email)
            ->setActive($data->isActive ?? false)
            ->setRoles(($data->isAdmin ?? false) ? ['ROLE_ADMIN'] : [])
        ;
        if ($data->password) {
            $user->setPassword($passwordEncoder->encodePassword($user, $data->password));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(\json_encode($user), 200);
    }

    public function disabledUser(string $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        $data = \json_decode($request->getContent());
        if (!($data->yesIamSure ?? false)) {
            return $this->dataValidationErrorResponse('yesIamSure', "Vous devez fournir l'argument yesIamSure à true");
        }
        if ($this->userRepository->isLastAdmin($user->getUsername())) {
            return $this->dataValidationErrorResponse('isActive', "Vous ne pouvez pas désactiver cet utilisateur, c'est le dernier administrateur !");
        }

        $user->setActive(false);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(\json_encode($user), 200);
    }

    public function enableUser(string $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        $data = \json_decode($request->getContent());
        if (!($data->yesIamSure ?? false)) {
            return $this->dataValidationErrorResponse('yesIamSure', "Vous devez fournir l'argument yesIamSure à true");
        }

        $user->setActive(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(\json_encode($user), 200);
    }

    /**
     * Delete user form veiw
     */
    public function deleteUser(string $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        $data = \json_decode($request->getContent());
        if (!($data->yesIamSure ?? false)) {
            return $this->dataValidationErrorResponse('yesIamSure', "Vous devez fournir l'argument yesIamSure à true");
        }
        if ($this->userRepository->isLastAdmin($user->getUsername())) {
            return $this->dataValidationErrorResponse('isActive', "Vous ne pouvez pas désactiver cet utilisateur, c'est le dernier administrateur !");
        }

        $this->userRepository->purge($user);

        return new JsonResponse(null, 200);
    }

    /**
     * Log view
     */
    public function displayLog(KernelInterface $kernel): JsonResponse
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $logDir = $kernel->getLogDir();

        $latestCTime = 0;
        $latestLogfile = '';
        if ($dirHandle = \dir($logDir)) {
            while (false !== ($entry = $dirHandle->read())) {
                $filepath = "{$logDir}/{$entry}";
                if (\is_file($filepath) && \filectime($filepath) > $latestCTime) {
                    $latestCTime = \filectime($filepath);
                    $latestLogfile = $entry;
                }
            }
        }

        return new JsonResponse(\file("{$logDir}/{$latestLogfile}", \FILE_IGNORE_NEW_LINES), 200);
    }
}
