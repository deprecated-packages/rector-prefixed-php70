<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

use Rector\Symfony\Contract\Tag\TagInterface;
final class ServiceDefinition
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string|null
     */
    private $class;
    /**
     * @var bool
     */
    private $isPublic;
    /**
     * @var bool
     */
    private $isSynthetic;
    /**
     * @var string|null
     */
    private $alias;
    /**
     * @var mixed[]
     */
    private $tags;
    /**
     * @param TagInterface[] $tags
     * @param string|null $class
     * @param string|null $alias
     */
    public function __construct(string $id, $class, bool $isPublic, bool $isSynthetic, $alias, array $tags)
    {
        $this->id = $id;
        $this->class = $class;
        $this->isPublic = $isPublic;
        $this->isSynthetic = $isSynthetic;
        $this->alias = $alias;
        $this->tags = $tags;
    }
    public function getId() : string
    {
        return $this->id;
    }
    /**
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }
    public function isPublic() : bool
    {
        return $this->isPublic;
    }
    public function isSynthetic() : bool
    {
        return $this->isSynthetic;
    }
    /**
     * @return string|null
     */
    public function getAlias()
    {
        return $this->alias;
    }
    /**
     * @return TagInterface[]
     */
    public function getTags() : array
    {
        return $this->tags;
    }
    /**
     * @return \Rector\Symfony\Contract\Tag\TagInterface|null
     */
    public function getTag(string $name)
    {
        foreach ($this->tags as $tag) {
            if ($tag->getName() !== $name) {
                continue;
            }
            return $tag;
        }
        return null;
    }
}
