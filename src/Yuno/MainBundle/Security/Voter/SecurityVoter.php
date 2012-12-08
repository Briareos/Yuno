<?php

namespace Yuno\MainBundle\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Yuno\MainBundle\Entity\User;
use Yuno\MainBundle\Entity\Click;
use Yuno\MainBundle\Entity\Campaign;
use Yuno\MainBundle\Entity\Banner;
use Yuno\MainBundle\Entity\Site;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SecurityVoter implements VoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return ($class !== 'CG\Proxy\MethodInvocation' && $class !== 'Symfony\Component\HttpFoundation\Request');
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        /** @var $roleObjects \Symfony\Component\Security\Core\Role\Role[] */
        $roleObjects = $token->getRoles();
        $roles = array();
        foreach ($roleObjects as $roleObject) {
            $roles[$roleObject->getRole()] = $roleObject->getRole();
        }

        if (isset($roles['ROLE_SUPER_ADMIN'])) {
            return self::ACCESS_GRANTED;
        }

        if (is_object($object) && !$this->supportsClass(get_class($object))) {
            return self::ACCESS_ABSTAIN;
        }

        if ($object instanceof Site || $object instanceof Banner || $object instanceof Campaign || $object instanceof Click || $object instanceof User) {
            // Just so our IDE doesn't complain.
            $base = '';
            $ownerId = 0;
            if ($object instanceof Site) {
                $base = 'SITE';
                $ownerId = $object->getUser()->getId();
            } elseif ($object instanceof Banner) {
                $base = 'BANNER';
                $ownerId = $object->getSite()->getUser()->getId();
            } elseif ($object instanceof Campaign) {
                $base = 'CAMPAIGN';
                $ownerId = $object->getUser()->getId();
            } elseif ($object instanceof Click) {
                $base = 'CLICK';
                $ownerId = $object->getCampaign()->getUser()->getId();
            } elseif ($object instanceof User) {
                $base = 'USER';
                $ownerId = $object->getId();
            }
            foreach ($attributes as $attribute) {
                if (!$this->canAccess($attribute, $base, $ownerId, $token->getUser(), $object, $roles)) {
                    return self::ACCESS_DENIED;
                }

            }

            return self::ACCESS_GRANTED;

        } elseif (is_object($object)) {
            throw new \RuntimeException(sprintf('Non-tracked object passed, an instance of "%s" with the following role attributes: "%s".', get_class($object), implode('", "', $attributes)));
        }


        return self::ACCESS_ABSTAIN;
    }

    private function canAccess($attribute, $base, $ownerId, User $user, $object, $roles)
    {
        if (in_array($attribute, array('VIEW', 'EDIT', 'DELETE'))) {
            if ($attribute === 'VIEW') {
                $attribute = 'LIST';
            }
            $globalScope = sprintf('ROLE_%s_%s_ALL', $base, $attribute);
            $localScope = sprintf('ROLE_%s_%s_OWN', $base, $attribute);
            if (isset($roles[$globalScope])) {
                return true;
            } elseif (isset($roles[$localScope]) && $ownerId === $user->getId()) {
                return true;
            }

            return false;
        } else {
            throw new \RuntimeException(sprintf('Instances of %s don\'t support the following role attribute: "%s".', get_class($object), $attribute));
        }
    }
}