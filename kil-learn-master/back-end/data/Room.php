<?php

$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/data/FloorPlan.php");

class Room
{

    /** @var array|null hard-coded array of room data */
    private static array|null $ROOMS = null;

    public static function getRooms(): array
    {
        if (self::$ROOMS == null) {
            self::setRooms();
        }
        return self::$ROOMS;
    }

    private static function setRooms(): void
    {
        self::$ROOMS = [
            new Room("2.9", FloorPlan::getFloorPlan(FloorPlan::$SECOND_FLOOR), "green-room.jpeg"),
            new Room("Lecture Theatre 1.1", FloorPlan::getFloorPlan(FloorPlan::$LOWER_FIRST_FLOOR), "lecture-theatre-1.1.jpeg"),
            new Room("Lecture Theatre 1.3", FloorPlan::getFloorPlan(FloorPlan::$FIRST_FLOOR), "lecture-theatre-1.3.jpeg"),
            new Room("Super Lab", FloorPlan::getFloorPlan(FloorPlan::$FIRST_FLOOR), "superlab.jpeg"),
            new Room("Collab 1", FloorPlan::getFloorPlan(FloorPlan::$LOWER_FIRST_FLOOR), "collab-1.jpeg"),
            new Room("LF8", FloorPlan::getFloorPlan(FloorPlan::$LOWER_FIRST_FLOOR), "lf8.jpeg")
        ];
    }

    public static function selectRoom(): Room
    {
        return self::getRooms()[rand(0, sizeof(Room::$ROOMS) - 1)];
    }

    public static function get(string $name): Room|null
    {
        foreach (self::getRooms() as $room) {
            if ($room->getName() == $name) {
                return $room;
            }
        }
        return null;
    }

    private string $name;
    /** @var FloorPlan parent floor plan of this room */
    private FloorPlan $parent;
    private string $imageId;

    public function __construct(string $name, FloorPlan $floorPlan, string $imageId)
    {
        $this->name = $name;
        $this->parent = $floorPlan;
        $this->imageId = $imageId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParent(): FloorPlan
    {
        return $this->parent;
    }

    public function getImagePath(): string
    {
        return "resources/images/rooms/$this->imageId";
    }


}