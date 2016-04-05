<?php
namespace Mongolid;

use Mongolid\DataMapper\DataMapper;
use Mongolid\Container\Ioc;
use Mongolid\Model\Attributes;
use Mongolid\Model\Relations;

/**
 * The Mongolid\ActiveRecord base class will ensure to enable your entity to
 * have methods to interact with the database. It means that 'save', 'insert',
 * 'update', 'where', 'first' and 'all' are available within every instance.
 * The Mongolid\Schema that describes the entity will be generated on the go
 * based on the $fields.
 *
 * @package  Mongolid
 */
abstract class ActiveRecord
{
    use Attributes, Relations;

    /**
     * Name of the collection where this kind of Entity is going to be saved or
     * retrieved from
     * @var string
     */
    public $collection = 'mongolid';

    /**
     * Describes the Schema fields of the model. Optionally you can set it to
     * the name of a Schema class to be used.
     * @see  Mongolid\Schema::$fields
     * @var  string|string[]
     */
    protected $fields  = [
        '_id' => 'objectId'
    ];

    /**
     * The $dynamic property tells if the object will accept additional fields
     * that are not specified in the $fields property. This is usefull if you
     * doesn't have a strict document format or if you want to take full
     * advantage of the "schemaless" nature of MongoDB.
     * @var boolean
     */
    public $dynamic = true;

    /**
     * Saves this object into database
     *
     * @return boolean Success
     */
    public function save()
    {
        return $this->getDataMapper()->save($this);
    }

    /**
     * Insert this object into database
     *
     * @return boolean Success
     */
    public function insert()
    {
        return $this->getDataMapper()->insert($this);
    }

    /**
     * Updates this object in database
     *
     * @return boolean Success
     */
    public function update()
    {
        return $this->getDataMapper()->update($this);
    }

    /**
     * Deletes this object in database
     *
     * @return boolean Success
     */
    public function delete()
    {
        return $this->getDataMapper()->delete($this);
    }

    /**
     * Gets a cursor of this kind of entities that matches the query from the
     * database
     *
     * @param  array  $query
     *
     * @return \Mongolid\Cursor\Cursor
     */
    public static function where(array $query = [])
    {
        return Ioc::make(get_called_class())
            ->getDataMapper()->where($query);
    }

    /**
     * Gets a cursor of this kind of entities from the database
     *
     * @return \Mongolid\Cursor\Cursor
     */
    public static function all()
    {
        return Ioc::make(get_called_class())
            ->getDataMapper()->all();
    }

    /**
     * Gets the first entity of this kind that matches the query
     *
     * @param  mixed  $query
     *
     * @return ActiveRecord
     */
    public static function first($query = [])
    {
        return Ioc::make(get_called_class())
            ->getDataMapper()->first($query);
    }

    /**
     * Handle dynamic method calls into the model.
     * @codeCoverageIgnore
     *
     * @param  string  $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, ['all', 'first', 'where'])) {
            return call_user_func_array([static::class, $method], $parameters);
        }

        return call_user_func_array([$this, $method], $parameters);
    }

    /**
     * Returns a DataMapper configured with the Schema and collection described
     * in this entity
     *
     * @return DataMapper
     */
    public function getDataMapper()
    {
        $dataMapper = Ioc::make(DataMapper::class);
        $dataMapper->schema = $this->getSchema();

        return $dataMapper;
    }

    /**
     * Returns a Schema object that describes this Entity in MongoDB
     *
     * @return Schema
     */
    protected function getSchema()
    {
        if (is_string($this->fields)) {
            return Ioc::make($this->fields);
        }

        $schema = new DynamicSchema;
        $schema->entityClass = get_class($this);
        $schema->fields      = $this->fields;
        $schema->dynamic     = $this->dynamic;
        $schema->collection  = $this->collection;

        return $schema;
    }
}