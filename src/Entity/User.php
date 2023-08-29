<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[UniqueEntity(fields: ['email'], message: 'An account already exist with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column("idUser")]
    private ?int $idUser = null;

    #[ORM\Column(length: 180, unique: true)]
    // #[Assert\Length(min: 2, minMessage: "The email must contain {{ limit }} minimum characters")]
    // #[Assert\Length(max: 30, maxMessage: "The email must contain {{ limit }} maximum characters")]
    private ?string $email = null;

    #[ORM\Column("lastName", length: 180)]
    #[Assert\Length(min: 2, minMessage: "Your last name must contain {{ limit }} minimum characters")]
    #[Assert\Length(max: 30, maxMessage: "Your last name must contain {{ limit }} maximum characters")]
    private ?string $lastName = null;

    #[ORM\Column("firstName", length: 180)]
    #[Assert\Length(min: 2, minMessage: "Your first name must contain {{ limit }} minimum characters")]
    #[Assert\Length(max: 30, maxMessage: "Your first name must contain {{ limit }} maximum characters")]
    private ?string $firstName = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 180)]
    #[Assert\Length(min: 5, minMessage: "Your address name must contain {{ limit }} minimum characters")]
    #[Assert\Length(max: 100, maxMessage: "Your address name must contain {{ limit }} maximum characters")]
    private ?string $address = null;

    #[ORM\Column(length: 180)]
    #[Assert\Length(min: 3, minMessage: "The city name must contain {{ limit }} minimum characters")]
    #[Assert\Length(max: 30, maxMessage: "The city name must contain {{ limit }} maximum characters")]
    private ?string $city = null;

    #[ORM\Column("postalCode", length: 6)]
    #[Assert\Length(min: 6, minMessage: "The postal code must contain {{ limit }} minimum characters")]
    #[Assert\Length(max: 6, maxMessage: "The city name must contain {{ limit }} maximum characters")]
    #[Assert\Regex(pattern: "/^[ABCEGHJ-NPRSTVXY]\d[ABCEGHJ-NPRSTV-Z][ -]?\d[ABCEGHJ-NPRSTV-Z]\d$/i", message: "The postal code must respect the Canadian format")]
    private ?string $postalCode = null;

    #[ORM\Column(length: 2)]
    private ?string $province = null;

    #[ORM\Column(length: 10)]
    #[Assert\Regex(pattern: "/^[0-9]{3}[0-9]{3}[0-9]{4}$/", message: "Your phone must respect the Canadian format")]
    private ?string $phone = null;

    #[ORM\Column]
    private array $roles = [];

    // ---

    #[Assert\Length(min: 6, minMessage: "Your password name must contain {{ limit }} minimum characters")]
    private ?string $newPassword = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Order::class, fetch: "LAZY")]
    private Collection $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    // public function getIdUser(): ?int
    // {
    //     return $this->idUser;
    // }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getProvince(): string
    {
        return $this->province;
    }

    public function setProvince(string $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    // ---- Update Password

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    public function isNewPasswordValid($currentHashedPassword, $newPassword): bool
    {
        var_dump($this->password, $currentHashedPassword, $newPassword);
        die();
        if ($currentHashedPassword == $this->password && $newPassword != $this->password)
            return true;
        return false;
    }

    // ---- TP05
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;

        $this->newPassword = null;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }
}
