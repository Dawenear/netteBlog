<?php

namespace App\Model;

use DateTime;
use Nette;
use Nette\Database\Context;
use Nette\SmartObject;

class EventManager
{
    use SmartObject;
    const TABLE_EVENTS = 'web_events';

    /** @var Context */
    private $database;

    const TABLE_STORY = 'web_story';

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function getEvents()
    {
        return $this->database->table(self::TABLE_EVENTS)->where('start > ?', new DateTime())->order('start')->limit(50);
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function getEndedEvents()
    {
        return $this->database->table(self::TABLE_EVENTS)->where('end < ?', new DateTime())->order('start DESC')->limit(50);
    }

    public function updateEvent($id, $values)
    {
        if ($values->story_id === 'new') {
            $story = $this->database->table(self::TABLE_STORY)->insert(['name' => $values->story_name]);
            $values->story_id = $story->id;
            $values->part = 1;
        } elseif ($values->story_id == 0) {
            $values->story_id = null;
        } else {
            $values->part = ($this->database->table(self::TABLE_EVENTS)->where('story_id = ? AND start < ?', $values->story_id, $values->start)->count() + 1);
        }

        unset($values->story_name);
        $this->database->table(self::TABLE_EVENTS)->where('id = ?', $id)->update($values);
    }

    public function addEvent($values)
    {
        if ($values->story_id === 'new') {
            $story = $this->database->table(self::TABLE_STORY)->insert(['name' => $values->story_name]);
            $values->story_id = $story->id;
            $values->part = 1;
        } elseif ($values->story_id == 0) {
            $values->story_id = null;
        } else {
            $values->part = ($this->database->table(self::TABLE_EVENTS)->where('story_id = ? AND start < ?', $values->story_id, $values->start)->count() + 1);
        }

        unset($values->story_name);
        $this->database->table(self::TABLE_EVENTS)->insert($values);
    }

    /**
     * @param $id
     *
     * @return false|Nette\Database\Table\ActiveRow
     */
    public function getEvent($id)
    {
        return $this->database->table(self::TABLE_EVENTS)->get($id);
    }

    public function getCurrentEvents()
    {
        return $this->database->table(self::TABLE_EVENTS)->where('start < ? AND end > ?', new DateTime(), new DateTime())->order('start')->limit(50)->fetchAll();
    }

    public function getStoryEvents($id)
    {
        return $this->database->table(self::TABLE_EVENTS)->where('story_id = ?', $id)->order('start DESC')->limit(50)->fetchAll();
    }

    public function getAllStories()
    {
        return $this->database->table(self::TABLE_STORY)->fetchAll();
    }

    public function orderStories($stories)
    {
        $ordered = [];
        $ordered[''] = 'Jednorázový';
        $ordered['new'] = 'Nový';
        foreach ($stories as $story) {
            $ordered[$story->id] = $story->name;
        }
        return $ordered;
    }
}
