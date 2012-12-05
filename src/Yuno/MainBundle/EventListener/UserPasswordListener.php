<?php

namespace Yuno\MainBundle\EventListener;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Yuno\MainBundle\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class UserPasswordListener
{
    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof User) {
            /** @var $entity User */
            $this->updatePassword($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof User) {
            /** @var $entity User */
            $this->updatePassword($entity);
            $args->getEntityManager()->getUnitOfWork()->recomputeSingleEntityChangeSet($args->getEntityManager()->getClassMetadata(get_class($entity)), $entity);
        }
    }

    public function updatePassword(User $user)
    {
        if ($user->getPlainPassword() !== null) {
            /** @var $encoder \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface */
            $encoder = $this->encoderFactory->getEncoder($user);
            $user->setPassword($encoder->encodePassword($user->getPlainPassword(), $user->getSalt()));
            $user->eraseCredentials();
        }
    }
}