<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\SecurityBundle\Controller;

use Doctrine\ORM\NoResultException;
use Sulu\Bundle\SecurityBundle\Exception\UserNotInSystemException;
use Sulu\Bundle\SecurityBundle\Security\Exception\EmailTemplateException;
use Sulu\Bundle\SecurityBundle\Security\Exception\InvalidTokenException;
use Sulu\Bundle\SecurityBundle\Security\Exception\MissingPasswordException;
use Sulu\Bundle\SecurityBundle\Security\Exception\NoTokenFoundException;
use Sulu\Bundle\SecurityBundle\Security\Exception\TokenEmailsLimitReachedException;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Security\Authentication\UserInterface as SuluUserInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

/**
 * Class ResettingController.
 */
class ResettingController extends Controller
{
    protected static $resetRouteId = 'sulu_admin.reset';

    /**
     * The interval in which the token is valid.
     *
     * @return \DateInterval
     */
    private static function getResetInterval()
    {
        return new \DateInterval('PT24H');
    }

    /**
     * The interval in which only one token can be generated.
     *
     * @return \DateInterval
     */
    private static function getRequestInterval()
    {
        return new \DateInterval('PT10M');
    }

    /**
     * Generates a token for a user and sends an email with
     * a link to the resetting route.
     *
     * @return JsonResponse
     */
    public function sendEmailAction(Request $request)
    {
        try {
            /** @var UserInterface $user */
            $user = $this->findUser($request->get('user'));
            $token = $this->generateTokenForUser($user);
            $email = $this->getEmail($user);
            $this->sendTokenEmail($user, $this->getSenderAddress($request), $email, $token);
        } catch (\Exception $ex) {
            // do nothing
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Resets a users password.
     *
     * @return JsonResponse
     */
    public function resetAction(Request $request)
    {
        try {
            $token = $request->get('token');

            if (null == $token) {
                throw new NoTokenFoundException();
            }

            /** @var UserInterface $user */
            $user = $this->findUserByValidToken($this->generateTokenHash($token));
            $this->changePassword($user, $request->get('password', ''));
            $this->deleteToken($user);
            $this->loginUser($user, $request);
            $response = new JsonResponse(['url' => $this->get('router')->generate('sulu_admin')]);
        } catch (InvalidTokenException $ex) {
            $response = new JsonResponse($ex->toArray(), 400);
        } catch (MissingPasswordException $ex) {
            $response = new JsonResponse($ex->toArray(), 400);
        } catch (NoTokenFoundException $ex) {
            $response = new JsonResponse($ex->toArray(), 400);
        }

        return $response;
    }

    /**
     * Returns the sender's email address.
     *
     * @return string
     */
    protected function getSenderAddress(Request $request)
    {
        $sender = $this->getParameter('sulu_security.reset_password.mail.sender');

        if (!$sender || !$this->isEmailValid($sender)) {
            $sender = 'no-reply@' . $request->getHost();
        }

        return $sender;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    protected function isEmailValid($email)
    {
        $constraint = new EmailConstraint();
        $result = $this->get('validator')->validate($email, $constraint);

        return 0 === \count($result);
    }

    /**
     * @return Translator
     */
    protected function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     * @return string
     */
    protected function getSubject()
    {
        return $this->getTranslator()->trans(
            $this->getParameter('sulu_security.reset_password.mail.subject'),
            [],
            $this->getParameter('sulu_security.reset_password.mail.translation_domain')
        );
    }

    /**
     * @param UserInterface $user
     *
     * @return string
     *
     * @throws EmailTemplateException
     */
    protected function getMessage($user, $token)
    {
        $resetUrl = $this->generateUrl(
            static::$resetRouteId,
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $template = $this->getParameter('sulu_security.reset_password.mail.template');
        $translationDomain = $this->getParameter('sulu_security.reset_password.mail.translation_domain');

        if (!$this->get('templating')->exists($template)) {
            throw new EmailTemplateException($template);
        }

        return \trim(
            $this->renderView(
                $template,
                [
                    'user' => $user,
                    'reset_url' => $resetUrl,
                    'translation_domain' => $translationDomain,
                ]
            )
        );
    }

    /**
     * Returns the users email or as a fallback the installation-email-adress.
     *
     * @return string
     */
    private function getEmail(UserInterface $user)
    {
        if (null !== $user->getEmail()) {
            return $user->getEmail();
        }

        return $this->container->getParameter('sulu_admin.email');
    }

    /**
     * Finds a user with an identifier (username or email).
     *
     * @param string $identifier
     *
     * @return UserInterface
     *
     * @throws EntityNotFoundException
     * @throws UserNotInSystemException
     */
    private function findUser($identifier)
    {
        try {
            $user = $this->getUserRepository()->findUserByIdentifier($identifier);
        } catch (NoResultException $exc) {
            throw new EntityNotFoundException($this->getUserRepository()->getClassName(), $identifier);
        }

        if (!$this->hasSystem($user)) {
            throw new UserNotInSystemException($this->getSystem(), $identifier);
        }

        return $user;
    }

    /**
     * Returns a user for a given token and checks if the token is still valid.
     *
     * @param string $token
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     *
     * @throws InvalidTokenException
     */
    private function findUserByValidToken($token)
    {
        try {
            /** @var UserInterface $user */
            $user = $this->getUserRepository()->findUserByToken($token);
            if (new \DateTime() > $user->getPasswordResetTokenExpiresAt()) {
                throw new InvalidTokenException($token);
            }

            return $user;
        } catch (NoResultException $exc) {
            throw new InvalidTokenException($token);
        }
    }

    /**
     * @return \Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface
     */
    private function getTokenGenerator()
    {
        return $this->get('sulu_security.token_generator');
    }

    /**
     * Gives a user a token, so she's logged in.
     *
     * @param $request
     */
    private function loginUser(UserInterface $user, $request)
    {
        $token = new UsernamePasswordToken($user, null, 'admin', $user->getRoles());
        $this->get('security.token_storage')->setToken($token); //now the user is logged in

        //now dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);
    }

    /**
     * Deletes the user's reset-password-token.
     */
    private function deleteToken(UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager();
        $user->setPasswordResetToken(null);
        $user->setPasswordResetTokenExpiresAt(null);
        $user->setPasswordResetTokenEmailsSent(null);
        $em->persist($user);
        $em->flush();
    }

    /**
     * Sends the password-reset-token of a user to an email-adress.
     *
     * @param string $from From-Email-Address
     * @param string $to To-Email-Address
     *
     * @throws NoTokenFoundException
     * @throws TokenEmailsLimitReachedException
     */
    private function sendTokenEmail(UserInterface $user, $from, $to, $token)
    {
        $maxNumberEmails = $this->getParameter('sulu_security.reset_password.mail.token_send_limit');

        if (new \DateTime() < $user->getPasswordResetTokenExpiresAt() && $user->getPasswordResetTokenEmailsSent() === \intval($maxNumberEmails)) {
            throw new TokenEmailsLimitReachedException($maxNumberEmails, $user);
        }
        $mailer = $this->get('mailer');
        $em = $this->getDoctrine()->getManager();
        $message = $mailer->createMessage()->setSubject(
                $this->getSubject()
            )->setFrom($from)->setTo($to)->setBody(
                $this->getMessage($user, $token)
            );
        $mailer->send($message);
        $user->setPasswordResetTokenEmailsSent($user->getPasswordResetTokenEmailsSent() + 1);
        $em->persist($user);
        $em->flush();
    }

    /**
     * Changes the password of a user.
     *
     * @param string $password
     *
     * @throws MissingPasswordException
     */
    private function changePassword(UserInterface $user, $password)
    {
        if ('' === $password) {
            throw new MissingPasswordException();
        }
        $em = $this->getDoctrine()->getManager();
        $user->setPassword($this->encodePassword($user, $password, $user->getSalt()));
        $em->persist($user);
        $em->flush();
    }

    /**
     * Generates a new token for a new user.
     *
     * @return string
     */
    private function generateTokenForUser(UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager();

        $token = $this->getToken();
        $user->setPasswordResetToken($this->generateTokenHash($token));
        $expireDateTime = (new \DateTime())->add(self::getResetInterval());
        $user->setPasswordResetTokenExpiresAt($expireDateTime);

        $em->persist($user);
        $em->flush();

        return $token;
    }

    /**
     * Generates a hash for the specified token.
     *
     * @return string
     */
    private function generateTokenHash($token)
    {
        return \hash('sha1', $this->container->getParameter('kernel.secret') . $token);
    }

    /**
     * Returns a unique token.
     *
     * @return string the unique token
     */
    private function getToken()
    {
        return $this->getUniqueToken($this->getTokenGenerator()->generateToken());
    }

    /**
     * If the passed token is unique returns it back otherwise returns a unique token.
     *
     * @param string $startToken The token to start width
     *
     * @return string a unique token
     */
    private function getUniqueToken($startToken)
    {
        try {
            $this->getUserRepository()->findUserByToken($startToken);
        } catch (NoResultException $ex) {
            return $startToken;
        }

        return $this->getUniqueToken($this->getTokenGenerator()->generateToken());
    }

    /**
     * Returns an encoded password gor a given one.
     *
     * @param string $password
     * @param string $salt
     */
    private function encodePassword(UserInterface $user, $password, $salt)
    {
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);

        return $encoder->encodePassword($password, $salt);
    }

    /**
     * @return UserRepositoryInterface
     */
    private function getUserRepository()
    {
        return $this->get('sulu.repository.user');
    }

    /**
     * Check if given user has sulu-system.
     *
     * @return bool
     */
    private function hasSystem(SuluUserInterface $user)
    {
        $system = $this->getSystem();
        foreach ($user->getRoleObjects() as $role) {
            if ($role->getSystem() === $system) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns system name.
     *
     * @return string
     */
    private function getSystem()
    {
        return $this->container->getParameter('sulu_security.system');
    }
}
