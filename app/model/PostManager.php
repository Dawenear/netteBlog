<?php

namespace App\Model;

use Nette;

class PostManager
{
    use Nette\SmartObject;

    const
        TABLE_POSTS = 'web_post',
        COLUMN_TITLE = 'title',
        COLUMN_CONTENT = 'content',
        COLUMN_CREATED = 'created',
        COLUMN_AUTHOR_ID = 'author_id';

    /**
     * @var Nette\Database\Context
     */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param $limit
     * @param $offset
     *
     * @return Nette\Database\ResultSet
     */
    public function getPosts($limit, $offset)
    {
        return $this->database->query('
            SELECT web_post.*, account.username FROM ' . self::TABLE_POSTS . '
            LEFT JOIN account ON web_post.author_id = account.id
            WHERE created < ?
            ORDER BY created DESC
            LIMIT ?
            OFFSET ?',
            new \DateTime, $limit, $offset
        );
    }

    /**
     * @return int
     */
    public function getPublishedPostsCount()
    {
        return $this->database->fetchField('SELECT COUNT(*) FROM ' . self::TABLE_POSTS . ' WHERE created < ?', new \DateTime);
    }

    /**
     * @param array
     *
     * @return void
     */
    public function addPost($values)
    {
        $post = [
            self::COLUMN_TITLE => $values->title,
            self::COLUMN_CONTENT => $values->content,
            self::COLUMN_AUTHOR_ID => 2,
        ];
        $values->created ? $post[self::COLUMN_CREATED] = $values->created : null;
        $post = $this->database->table(self::TABLE_POSTS)->insert($post);
    }

    public function deletePost($id)
    {
        $this->database->table(self::TABLE_POSTS)
            ->where('id', $id)
            ->delete();
    }

    public function getPost($id)
    {
        return $this->database->table(self::TABLE_POSTS)->get($id);
    }
}
