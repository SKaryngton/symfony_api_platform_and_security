<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\CheeseListingRepository;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CheeseListingRepository::class)]
#[ApiResource (
    shortName: 'Cheeses',
    description: 'A rare and valuable Cheese',
    operations: [
        new Get(
            uriTemplate: "/cheeses/{id}",
            defaults: ['color'=> 'brown'],
            requirements: ['id'=>'\d+'],
            /*Lorsque j'obtient un seul élément, je souhaite inclure toutes les propriétés qui ont le cheeses:read groupe comme d'habitude. Mais je souhaite également
             inclure toutes les propriétés dans un nouveau cheese_listing:item:get groupe.*/
            normalizationContext: [
                'groups'=>['cheeses:read','cheeses:item:get']
            ]
        ),
        new GetCollection(),
        new Post(),
        new Put(security: "is_granted('EDIT')"),
        new Delete(security: "is_granted('DELETE')"),
        new Patch(security: "is_granted('ROLE_USER') and object.owner == user"),
    ],
    formats: ["jsonld","json","csv","html"],
    normalizationContext: [
        'groups'=>['cheeses:read']
    ],
    denormalizationContext: [
        'groups'=>['cheeses:write']
    ],
    paginationItemsPerPage: 5,
    security: "is_granted('ROLE_USER')"

)]
#[ApiResource(
    uriTemplate: '/users/{id}/cheeses',
    operations: [new GetCollection()],
    uriVariables: [
        'id' => new Link(fromProperty: 'cheeseListings', fromClass: User::class)
    ]
)]

#[ApiFilter(BooleanFilter::class,properties: ['isPublished'])]
#[ApiFilter(SearchFilter::class,properties: ["title"=>"partial","description"=>"partial"])]
#[ApiFilter(RangeFilter::class,properties: ["price"])]
#[ApiFilter(PropertyFilter::class)]

class CheeseListing
{

    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['cheeses:read','cheeses:write','users:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50, maxMessage: "Describe your cheese in 50 chars or less")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['cheeses:read'])]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['cheeses:read','cheeses:write','users:read'])]
    #[Assert\NotBlank]
    private ?int $price = null;

    #[ORM\Column]
    #[Groups(['cheeses:read'])]
    private ?bool $isPublished = false;

    #[Gedmo\Slug(fields:['title','id'])]
    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['cheeses:read'])]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'cheeseListings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cheeses:read','cheeses:write'])]
    private ?User $owner = null;






    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    #[Groups(['cheeses:read'])]
    public function getShortDescription(): string
    {
        if (strlen($this->description) < 40) {
            return $this->description;
        }
        return substr($this->description, 0, 40).'...';
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    #[Groups(['cheeses:write'])]
    #[SerializedName('description')]
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }



    #[Groups(['cheeses:read'])]
    public function getCreatedAtAgo():string{
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
   }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }






}
