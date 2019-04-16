<?php

class Db extends PDO
{
    /**
     * @param int $postId
     * @return array
     * @throws Exception
     */
    public function getComments(int $postId): array
    {
        $sql = 'SELECT 
                  id, parent_id, post_id, text, DATE_FORMAT(created_at, "%d.%m.%Y %h:%i") AS date
                FROM comments
                WHERE post_id = :id';

        $commentsStatement = $this->prepare($sql);
        $commentsStatement->bindValue(':id', $postId, PDO::PARAM_INT);

        if (!$commentsStatement->execute()) {
            $error = $commentsStatement->errorInfo();
            throw new Exception('Ошибка: ' . $error[2]);
        }

        $comments = $commentsStatement->fetchAll(PDO::FETCH_ASSOC);

        return is_array($comments) ? $this->buildTree($comments) : [];
    }

    /**
     * @param int $post_id
     * @param string $text
     * @param int $parentCommentId
     * @return array
     * @throws Exception
     */
    public function insertComment(int $post_id, string $text, int $parentCommentId = 0): array
    {
        if ($parentCommentId) {

            $commentStatement = $this->prepare('SELECT id FROM comments WHERE id = :id');
            $commentStatement->bindValue(':id', $parentCommentId, PDO::PARAM_INT);
            $commentStatement->execute();

            $comment = $commentStatement->fetch(PDO::FETCH_ASSOC);

            if (!$comment) {
                throw new Exception('Комментарий не существует');
            }
        }

        $sql = 'INSERT INTO comments (parent_id, post_id, text)
                VALUES(:parent_id, :post_id, :text)';

        $newCommentStatement = $this->prepare($sql);
        $newCommentStatement->bindValue(':parent_id', $parentCommentId, PDO::PARAM_INT);
        $newCommentStatement->bindValue(':post_id', $post_id, PDO::PARAM_INT);
        $newCommentStatement->bindValue(':text', $text, PDO::PARAM_STR);

        if (!$newCommentStatement->execute()) {
            $error = $newCommentStatement->errorInfo();
            throw new Exception('Ошибка: ' . $error[2]);
        }

        return [
            'id' => $this->lastInsertId()
        ];
    }

    /**
     * @param array $array
     * @param int $parent_id
     * @return array
     */
    function buildTree(array $array, int $parent_id = 0): array
    {
        $array = array_combine(array_column($array, 'id'), array_values($array));
        foreach ($array as $k => &$v) {
            if (isset($array[$v['parent_id']])) {
                $array[$v['parent_id']]['children'][] = &$v;
            }
            unset($v);
        }
        $tree = array_filter($array, function($v) use ($parent_id) {
            return $v['parent_id'] == $parent_id;
        });

        return array_values($tree);
    }
}
