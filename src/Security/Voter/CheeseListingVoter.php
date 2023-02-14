<?php

namespace App\Security\Voter;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CheeseListingVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const VIEW = 'VIEW';
    public const POST = 'POST';
    public const DELETE = 'DELETE';

    public function __construct(private Security $security)
    {
    }


    protected function supports(string $attribute, mixed $subject): bool
    {

        return in_array($attribute, [self::EDIT, self::VIEW, self::POST, self::DELETE])
            && $subject instanceof \App\Entity\CheeseListing;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                 if( $subject->getOwner === $user){
                     return true;
                 }
                if( $this->security->isGranted('ROLE_ADMIN')){
                    return true;
                }
                return false;
            case  self::DELETE:
                if( $this->security->isGranted('ROLE_ADMIN')){
                    return true;
                }
                return false;

        }

        throw new \RuntimeException(sprintf('Unhandled attribute "%s"',$attribute));
    }
}
