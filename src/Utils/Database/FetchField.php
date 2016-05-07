<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils\Database;

// Utils.
use Behat\DebugExtension\Debugger;

class FetchField
{
    use Debugger;

    /**
     * @var \SelectQuery
     */
    private $query;

    /**
     * FetchField constructor.
     *
     * @param string $table
     * @param string $field
     */
    public function __construct($table, $field)
    {
        $this->query = db_select($table);
        $this->query->fields($table, [$field]);
    }

    /**
     * @param string $field
     * @param string|array $value
     * @param string $operator
     *
     * @return $this
     */
    public function condition($field, $value = null, $operator = null)
    {
        $this->query->condition($field, $value, $operator);

        return $this;
    }

    /**
     * @return string
     */
    public function execute()
    {
        self::debug(
            ['SQL query is: %s'],
            [trim(str_replace("\n", ' ', $this->query))]
        );

        return $this->query->execute()->fetchField();
    }
}
