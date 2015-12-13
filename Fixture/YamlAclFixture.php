<?php

namespace Khepin\YamlFixturesBundle\Fixture;

class YamlAclFixture extends AbstractFixture
{
    public function load($acl_manager, $tags = null)
    {
        if (!$this->hasTag($tags) || !isset($this->file['acl'])) {
            return;
        }

        foreach ($this->file['acl'] as $reference => $permissions) {
            foreach ($permissions as $user => $permission) {
                $acl_manager->setObjectPermission(
                    $this->loader->getReference($reference),
                    $this->getMask($permission),
                    $this->loader->getReference($user)
                );
            }
        }
    }

    /**
     * Retrieves the constant value for the given mask name.
     *
     * @param type $permission
     *
     * @return type
     */
    public function getMask($permission)
    {
        return constant('Symfony\Component\Security\Acl\Permission\MaskBuilder::'.$permission);
    }

    public function createObject($class, $data, $metadata, $options = [])
    {
        // No implementation for ACL fixtures
    }
}
