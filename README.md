<style>
green { color: #299660}
yel { color: #9ea647}
blue { color: #099fc0}
red {color: #ce4141}
fs { font-size: 13px}
</style>

# 👋 API3  [api_platform](https://api-platform.com/ "@embed")
___________________________________________________________
>
>  make api with api_platform. ![image](https://cdn.path.to/some/image.jpg "This is some image...")

<br>

* [x] `create new project` :  symfony new api
*  [x] `run new project` 
   - symfony serve 
   - symfony server:stop
   
   
- [ ] `installations` 
  1. composer require --dev symfony/profiler-pack 
  2. composer require debug
  3. composer require symfony/orm-pack 
  4. composer require --dev symfony/maker-bundle 

<br>

-  `installation api_platform` : composer require api
   -  `https://127.0.0.1:8000/api`
  
<br>


 
  <br>

- `make ApiResources`
  - symfony console make:entity 

| Class             | Mark as API pPlatform resource |         Props         |
|:------------------|:------------------------------:|:---------------------:|
| **CheeseListing** |        `#[ApiResource]`        |    `title`:string     |
|                   |                                |    `price`:integer    |
|                   |                                |  `description`:text   |
 |                   |                                | `isPublished`:boolean |
 
- **<green>Sluggable | timestample** : <blue>composer require "stof/doctrine-extensions-bundle"

>config/packages/stof_doctrine_extensions.yaml
>- <blue>stof_doctrine_extensions:
>  - <blue>default_locale: en_US
>  - <blue>orm:
>      - <blue>default:
>         - <blue>sluggable: true
>         - <blue>timestampable: true

> use Gedmo\Mapping\Annotation as Gedmo; <br>
> class CheeseListing
> {  <br>
>      use TimestampableEntity;
>
>      #[Gedmo\Slug(fields: ['title','id'])]
>      #[ORM\Column(length: 100, unique: true)]
>      private ?string $slug = null;
> }

- <yel>Database migration (`Docker`)
  - make sure you have a docker-compose.yml file
  - <blue>docker-compose up -d
  
  <br>
  
- <yel>Database migration (`Local Database`)
  - `.env` : connect a database
    - <blue>DATABASE_URL="mysql://root@127.0.0.1:3306/api_cheese?serverVersion=8&charset=utf8mb4"
    - <blue>symfony console d:d:c
  - <blue>composer require migrations
  - <blue>symfony console make:migration
  - <blue>symfony console d:m:m
  - query database on the terminal
     -   > ###### symfony console doctrine:query:sql 'SELECT * FROM cheeseListing'



<br>

- Failed Migrations After Adding new column
  - Dev
   > *  symfony console doctrine:schema:drop --full-database --force
   > * symfony console doctrine:database:drop --force
   > * symfony console d:m:m
  - Prod
      >   * Go to Migration version in function `up` and replace `NOT NULL` by `DEFAULT NULL` 
       >     *  ###### $this->addSql('ALTER TABLE question ADD created_at DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL');
      >   * Update the value in the database (with valid id if relation)
      >      * ######  $this->addSql('UPDATE question SET created_at = NOW(), updated_at = NOW()');
      >      * symfony console d:m:m
      >   * If <red>Error</red>:  An exception occurred while executing a query: SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name '...'
      >      * <blue>symfony console doctrine:query:sql 'ALTER TABLE table_name DROP COLUMN column_name'
      >      * <blue>symfony console d:m:m
      >   * Go to Migration version in function `up` and replace `DEFAULT NULL` by `NOT NULL` and remove the update Line
      >      * <blue>symfony console d:m:m
- Others Migration problem
    > <red>problem</red>:The metadata storage is not up to date, please run the sync-metadata-storage co     mmand to fix this issue.
    > * <green>solution:<br><blue>BASE_URL="mysql://root:@127.0.0.1:3306/main?serverVersion=mariadb-10.5.5"

<br>

- ### Api Operations
> #[ApiResource ( <br>
> **<yel>shortName**:'Dragon', `change api_resource name` <br>
>**<yel>description** :'A rare and valuable Treasure', `add description`<br>
>**<yel>operations**: [ <br>
> <yel>new Get(<br>
> ###### uriTemplate: "/cheeses/{id}",`change path`<br>
> ###### defaults: ['color'=> 'brown'],<br>
>###### requirements: ['id'=>'\d+']
>),`add operation`<br>
> <yel>new GetCollection(),<br>
> new Post(), <br>
> new Put(), <br>
> new Delete(), <br>
> new Patch(),<br>
>]
)]

- ### <green>Serializer
> works with Entities getters and setters.
> shows all getter for Get requests and all setters for Post request in Api
> so you can handel Api_resources by
>  -  removing or Adding setters and getter(`removing getters or setters is not recommanded`)
>  - Using  Serialization Groups
   1. <yel>Removing or Adding Setters and Getters
    
    - until now for Api Post Request
        {
        "title": "string", 
        "description": "string",
        "price": 0,
        "isPublished": true,
        "slug": "string",
        "createdAt": "2023-02-01T10:53:23.085Z",
        "updatedAt": "2023-02-01T10:53:23.085Z"
        }
    - create or remove new costum field for our ressources

    class CheeseListing 
    {
         public function setTextDescription(string $description): self
        {
        $this->description = nl2br($description);

        return $this;
        }
    }
    - after add the new function setTextDescription and removed  function setSlug and setDescription
    {
        "title": "string",
        "textDescription": "string",
        "price": 0,
        "isPublished": true,
        "createdAt": "2023-02-01T10:53:23.085Z",
        "updatedAt": "2023-02-01T10:53:23.085Z"
        }
   - ##### <yel>(serializer) change retrieving date format(adding getter)
    
    
          - composer require nesbot/carbon

           class CheeseListing
          {
            public function getCreatedAtAgo():string{
             return Carbon::instance($this->getCreatedAt())->diffForHumans();
           }
          }

    
         
2. <yel>Using  Serialization Groups ( normalization (Get) | denormalization (Post) )

        - use Symfony\Component\Serializer\Annotation\Groups;
         #[ApiResource (
         . . .
         normalizationContext: [
         'groups'=>['cheeses:read']
         ],
         denormalizationContext: [
         'groups'=>['cheeses:write']
         ],
      
          )]
          class CheeseListing
          {
           ...
           #[Groups(['cheeses:read','cheeses:write'])]
           private ?string $title = null;
        
           ..
           #[Groups(['cheeses:read'])]
           private ?string $description = null;
        
           . ..
           #[Groups(['cheeses:read','cheeses:write'])]
           private ?int $price = null;
        
           ...
           #[Groups(['cheeses:read'])]
           private ?bool $isPublished = null;
        
           ...
           #[Groups(['cheeses:read'])]
           private ?string $slug = null;
           ....
           #[Groups(['cheeses:write'])]
           #[SerializedName('description')]
           public function setTextDescription(string $description): self
           {
           $this->description = nl2br($description);
        
                return $this;
           }
           .....
            #[Groups(['cheeses:read'])]
           public function getCreatedAtAgo():string{
           return Carbon::instance($this->getCreatedAt())->diffForHumans();
           }
            ____________________________________________________________
            Post                   
            {
            "title": "string",
            "price": 0,
            "description": "string" (don't forget to add serializename)
            }
           _____________________________________________________________
            Get
              {
                "@context": "string",
                "@id": "string",
                "@type": "string",
                "title": "string",
                "description": "string",
                "price": 0,
                "isPublished": true,
                "slug": "string",
                "createdAtAgo": "string"
                }
            _____________________________________________________________
- ## <green>Filtering & Searching


| FILTER                                                                                                                                                   |                                                                              Generate URL |                
|:---------------------------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------:|
| #[ApiFilter(`BooleanFilter`::class,`properties: ['isPublished']`)]<br> search published or unpublished                                                   |                                   https://127.0.0.1:8000/api/cheeses?` &isPublished=true` |        
| #[ApiFilter(`SearchFilter`::class,`properties: ["title"=>"partial","description"=>"partial"]`)]<br> search where title and description contains a string |                          https://127.0.0.1:8000/api/cheeses?` &title=str&description=str` |        
| #[ApiFilter(`RangeFilter`::class,`properties: ["price"]`)]<br> search where price gt or lt ...                                                           | https://127.0.0.1:8000/api/cheeses?` &price[gt]=20 ([gte], [lt] ,[lte] , [between]=[1,4]` |    
| #[ApiFilter(`PropertyFilter`::class)]<br> just return properties you have given                                                                          |          https://127.0.0.1:8000/api/cheeses?` properties[]=title&roperties[]=description` |  


- ## <green>Pagination (for all Get request including filter and search)

      #[ApiResource (
      .......
      paginationItemsPerPage: 5

      )]  

- ## <green> Add Retrieving Formats 

       config/packages/api_platform.yaml
       
         api_platform:
            formats:
              jsonld:   ['application/ld+json']
              jsonhal:  ['application/hal+json']
              jsonapi:  ['application/vnd.api+json']
              json:     ['application/json']
              xml:      ['application/xml', 'text/xml']
              yaml:     ['application/x-yaml']
              csv:      ['text/csv']
              html:     ['text/html']
              myformat: ['application/vnd.myformat']

   - <yel>Activate just specific formats
           
          #[ApiResource (
          .......
          formats: ["jsonld","json","csv","html"],
          .......
          )]  
-  ## <green> Validation 
       
       use Symfony\Component\Validator\Constraints as Assert;

       class CheeseListing
       {

       #[Assert\NotBlank]
       #[Assert\Length(min: 2, max: 50, maxMessage: "Describe your cheese in 50 chars or less")]
       private ?string $title = null;
    
      
       #[Assert\NotBlank]
       private ?string $description = null;
    
      
       #[Assert\NotBlank]
       private ?int $price = null;

       }
  -  ## ADD a User(email, username, password) Entity
    
      - <blue>symfony console make:user
      - <blue>symfony console make:migration
      - <blue>symfony console d:m:m

            #[ApiResource(
            normalizationContext: [
            'groups'=>['users:read']
            ],
            denormalizationContext: [
            'groups'=>['users:write']
            ],
            )]
            #[UniqueEntity(fields: ['email','username'])]
            class User implements UserInterface, PasswordAuthenticatedUserInterface
            {
            .....

            #[ORM\Column(length: 180, unique: true)]
            #[Groups(['users:read','users:write'])]
            #[Assert\Email]
            private ?string $email = null;

            ....

             
            #[Groups(['users:write'])]
            private ?string $password = null;

            #[ORM\Column(length: 255, unique: true)]
            #[Groups(['users:read','users:write'])]
            #[Assert\NotBlank]
            private ?string $username = null;
            }

- ## ManyToOne Relation CheeseListing -> User (Embedded Relation)
     - <yel>Create CheeseListing  using IRI by owner
           
           #[Groups(['cheeses:read','cheeses:write'])]
           private ?User $owner = null;
  

           Post Request body
  
           {
           "title": "cheese 4",
           "price": 400,
           "owner": "/api/users/2",
           "description": "cheese 4"
           }
           {
           
           curl -X 'GET' \
            'https://127.0.0.1:8000/api/cheeses/4' \
            -H 'accept: application/ld+json'
  
           "@context": "/api/contexts/Cheeses",
           "@id": "/api/cheeses/4",
           "@type": "Cheeses",
           "title": "cheese 4",
           "description": "cheese 4",
           "price": 400,
           "isPublished": false,
           "slug": "cheese-4",
           "owner":                          "/api/users/2",
           "shortDescription": "cheese 4",
           "createdAtAgo": "8 minutes ago"
           }
     - <yel>retrieve cheeseListing user(data(email, username))  `add cheeses:read`


           class User ...
           {
           .....
           #[Groups(['users:read','users:write','cheeses:read'])]
           private ?string $email = null;

            ....
           #[Groups(['users:read','users:write','cheeses:read'])]
           private ?string $username = null;
           }
          
           curl -X 'GET' \
           'https://127.0.0.1:8000/api/cheeses/4' \
            -H 'accept: application/ld+json

           {
           "@context": "/api/contexts/Cheeses",
           "@id": "/api/cheeses/4",
           "@type": "Cheeses",
           "title": "cheese 4",
           "description": "cheese 4",
           "price": 400,
           "isPublished": false,
           "slug": "cheese-4",
           "owner": {

                              "@id": "/api/users/2",
                              "@type": "User",
                              "email": "user2@example.com",
                              "username": "user2"
           },
           "shortDescription": "cheese 4",
           "createdAtAgo": "40 minutes ago"
           }

  -  <yel>retrieve user cheesesListings(IRI)

            #[Groups(['users:read'])]
            private Collection $cheeseListings;
            

            curl -X 'GET' \
            'https://127.0.0.1:8000/api/users/2' \
            -H 'accept: application/ld+json'
  
            {
            "@context": "/api/contexts/User",
            "@id": "/api/users/2",
            "@type": "User",
            "email": "user2@example.com",
            "username": "user2",
            "cheeseListings": [

                                       "/api/cheeses/4"
            ]
            }
     - <yel>retrieve user cheesesListings(Data(price,title)) `add users:read`

           class CheeseListing
           {
           ...
           #[Groups(['cheeses:read','cheeses:write','users:read'])]
           private ?string $title = null;
           . ..
           #[Groups(['cheeses:read','cheeses:write','users:read'])]
           private ?int $price = null;
           .....
     

           curl -X 'GET' \
           'https://127.0.0.1:8000/api/users/2' \
           -H 'accept: application/ld+json'
           
            {
            "@context": "/api/contexts/User",
            "@id": "/api/users/2",
            "@type": "User",
             "email": "user2@example.com",
             "username": "user2",
             "cheeseListings": [
             {
                                   "@id": "/api/cheeses/4",
                                   "@type": "Cheeses",
                                   "title": "cheese 4",
                                   "price": 400
             }
             ]
             }
     - <yel>Show Uri by GetAll and Data by getOne</yel> create Group `cheeses:item:get`


            operations: [
            new GetCollection(),
            new Post(),
            new Get(
            normalizationContext: [ 'groups'=>['cheeses:read','cheeses:item:get']]
            ),
            new Patch(),
            ]
            class CheeseListing
            {}





            class User ....
            {
                
               
                #[Groups(['users:read','users:write','cheeses:item:get'])]
                private ?string $email = null;
                 .....
                #[Groups(['users:read','users:write','cheeses:item:get'])]
                private ?string $username = null;
                ......
            }


- ### <yel>Subresources

  
        #[ApiResource(
        uriTemplate: '/users/{id}/cheeses',
        operations: [new GetCollection()],
        uriVariables: [
        'id' => new Link(fromProperty: 'cheeseListings', fromClass: User::class)
         ]
        )]
        class CheeseListing
        {}


       curl -X 'GET' \
       'https://127.0.0.1:8000/api/users/1/cheeses?page=1' \
       -H 'accept: application/ld+json'

# <green>Api Security

  - ## Authentication 
    -  symfony console make:user 
    -  symfony console make:auth 
    - ### <yel>Encode password with the terminal

        - <blue>symfony console security encode
        - <blue>symfony console security:hash-password
  - ## Authorization
       - <yel>access control 
    ```
      #[ApiResource(
      operations: [
      new Get(security: "is_granted('ROLE_USER') and object == user"),
      new GetCollection(security: "is_granted('ROLE_ADMIN')"),
      new Post(security: "is_granted('PUBLIC_ACCESS')"),
      new Put(security: "is_granted('ROLE_USER') and object == user"),
      new Delete(security: "is_granted('ROLE_ADMIN')"),
      new Patch(security: "is_granted('ROLE_USER') and object == user"),
      ]
      security: "is_granted('ROLE_USER')",//default access operation unless the operation has his own security
      )]
      class User {}
   
        ____________________________________________________________________________________________________
    
      #[ApiResource (
      operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(security: "is_granted('ROLE_ADMIN') or object.owner == user"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_USER') and object.owner == user"),
      ],
      security: "is_granted('ROLE_USER')",//default access operation unless the operation has his own security
      )]
      class CheeseListing{}
         
     ```
     - voters 
       - <blue>symfony console make:voter
       - ```
         
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




           CheeseListing{} 
           new Put(security: "is_granted('EDIT')"),
           new Delete(security: "is_granted('DELETE')"),
       ```
     - 
  
  - ## Test
     - <blue>composer require test --dev
     - run test with <blue>php bin/phpunit
     - test database
        - ```
          .env.test
    
           DATABASE_URL="mysql://root@127.0.0.1:3306/api_cheese_test?serverVersion=mariadb-10.5.5"
      
          ```
        -   <blue>symfony console doctrine:database:create --env=test
        -  <blue>symfony console doctrine:schema:create --env=test 
     - 

# Utils

 - installation 
    - composer require orm-fixtures --dev
    - symfony console make:fixtures
