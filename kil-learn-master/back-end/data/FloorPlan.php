<?php

class FloorPlan
{

    public static string $GROUND_FLOOR = "Ground Floor";
    public static string $LOWER_FIRST_FLOOR = "Lower First Floor";
    public static string $FIRST_FLOOR = "First Floor";
    public static string $SECOND_FLOOR = "Second Floor";

    private static array|null $FLOOR_PLANS = null;

    public static function getFloorPlans(): array
    {
        if (self::$FLOOR_PLANS == null) {
            self::setFloorPlans();
        }
        return self::$FLOOR_PLANS;
    }

    public static function getFloorPlan(string $name): FloorPlan|null
    {
        foreach (self::getFloorPlans() as $floorPlan) {
            if ($floorPlan->getName() == $name) {
                return $floorPlan;
            }
        }
        return null;
    }

    private static function setFloorPlans(): void
    {
        self::$FLOOR_PLANS = [
            new FloorPlan(self::$GROUND_FLOOR, "ground-floor.png"),
            new FloorPlan(self::$LOWER_FIRST_FLOOR, "lower-first-floor.png"),
            new FloorPlan(self::$FIRST_FLOOR, "first-floor.png"),
            new FloorPlan(self::$SECOND_FLOOR, "second-floor.png")
        ];
    }

    private string $name;
    private string $imageId;

    public function __construct(string $name, string $imageId)
    {
        $this->name = $name;
        $this->imageId = $imageId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string the path of the floor plan image, relative to the root of the site
     */
    public function getImagePath(): string
    {
        return "resources/images/floor-plans/$this->imageId";
    }


}