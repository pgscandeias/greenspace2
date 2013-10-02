<?php
namespace My\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use My\MainBundle\Entity\Base;
use My\MainBundle\Entity\Player;


/**
 * @ORM\Entity(repositoryClass="My\MainBundle\Entity\FleetRepository")
 * @ORM\Table(name="fleets")
 */
class Fleet extends BaseEntity
{
    const DEFAULT_RANGE = 100;
    const DEFAULT_SPEED = 2;
    
    /**
     * @ORM\ManyToOne(targetEntity="Base", inversedBy="fleets")
     */
    private $base;
    public function setBase(Base $base) { $this->base = $base; return $this; }
    public function getBase() { return $this->base; }
    public function clearBase() { $this->base = null; return $this; }

    /**
     * @ORM\ManyToOne(targetEntity="Base", inversedBy="outbound")
     */
    private $origin;
    public function setOrigin(Base $origin) { $this->origin = $origin; return $this; }
    public function getOrigin() { return $this->origin; }
    public function clearOrigin() { $this->origin = null; return $this; }

    /**
     * @ORM\ManyToOne(targetEntity="Base", inversedBy="inbound")
     */
    private $destination;
    public function setDestination(Base $destination) { $this->destination = $destination; return $this; }
    public function getDestination() { return $this->destination; }
    public function clearDestination() { $this->destination = null; return $this; }

    /**
     * Distance to destination
     * @ORM\Column(type="integer", nullable=true)
     */
    private $distance = 0;
    public function setDistance($distance) { $this->distance = $distance; return $this; }
    public function getDistance() { return $this->distance; }
    public function move($distance) { $this->distance -= ceil($distance); return $this; }

    /**
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="fleets")
     */
    private $player;
    public function setPlayer(Player $player) { $this->player = $player; return $this; }
    public function getPlayer() { return $this->player; }

    /**
     * @ORM\Column(type="integer")
     */
    private $power = 0;
    public function setPower($power) { $this->power = $power; return $this; }
    public function getPower() { return $this->power; }
    public function removePower($power) { $this->power -= $power; return $this; }


    public function hasArrived()
    {
        return $this->destination && $this->distance <= 0;
    }

    public function isMoving()
    {
        return $this->distance != null && $this->destination && $this->origin;
    }

    public function getBaseId() { return $this->base ? $this->base->getId() : null; }
    public function getOriginId() { return $this->origin ? $this->origin->getId() : null; }
    public function getDestinationData()
    {
        return $this->destination ? 
        [
            'id' => $this->destination->getId(),
            'x' => $this->destination->getX(),
            'y'=> $this->destination->getY(),
            'timeleft' => $this->getTimeLeftToDestination(),
        ] : [
            'id' => null, 'x' => null, 'y' => null,
        ];
    }

    public function getCoords()
    {
        if ($this->getBase()) return [
            'x' => $this->getBase()->getX(),
            'y' => $this->getBase()->getY(),
        ];

        $origin = $this->getOrigin();
        $destination = $this->getDestination();
        $distance = $origin->getDistanceToBase($destination);
        $progress = 1 - $this->getDistance() / $distance;
        $x = 0;
        $y = 0;

        $xDistance = $destination->getX() - $origin->getX();
        $x = $origin->getX() + $xDistance * $progress;

        $yDistance = $destination->getY() - $origin->getY();
        $y = $origin->getY() + $yDistance * $progress;


        return ['x' => ceil($x), 'y' => ceil($y)];
    }

    public function getY()
    {
        if ($this->getBase()) return $this->getBase()->getY();
        return $this->getDestination()->getY() - $this->getOrigin()->getY();
    }

    protected function getTimeLeftToDestination()
    {
        if (!$this->destination) return;

        return $this->distance / Fleet::DEFAULT_SPEED;
    }
}