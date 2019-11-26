<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\SchemaMapper;

use Temando\Shipping\Rest\Response\DataObject\AbstractResource;
use Temando\Shipping\Rest\SchemaMapper\JsonApi\RelationshipHandler;
use Temando\Shipping\Rest\SchemaMapper\JsonApi\ResourceContainerInterface;
use Temando\Shipping\Rest\SchemaMapper\JsonApi\TypeMapInterface;
use Temando\Shipping\Rest\SchemaMapper\Reflection\PropertyHandlerInterface;
use Temando\Shipping\Rest\SchemaMapper\Reflection\TypeHandlerInterface;

/**
 * Temando REST API JSON API Parser
 *
 * This deserializer introduces very minimal JSON API support as required for handling
 * Temando platform responses:
 * - use type property to determine the deserialized type via type map
 * - resolve relationships (only if contained in message, no fetching of additional resource)
 *
 * @package  Temando\Shipping\Rest
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class Json extends AbstractParser implements ParserInterface
{
    /**
     * @var ResourceContainerInterface
     */
    private $resourceContainer;

    /**
     * @var TypeMapInterface
     */
    private $typeMap;

    /**
     * @var RelationshipHandler
     */
    private $relationshipHandler;

    /**
     * JsonApi constructor.
     * @param PropertyHandlerInterface $propertyHandler
     * @param TypeHandlerInterface $typeHandler
     * @param ResourceContainerInterface $resourceContainer
     * @param TypeMapInterface $typeMap
     * @param RelationshipHandler $relationshipHandler
     */
    public function __construct(
        PropertyHandlerInterface $propertyHandler,
        TypeHandlerInterface $typeHandler,
        ResourceContainerInterface $resourceContainer,
        TypeMapInterface $typeMap,
        RelationshipHandler $relationshipHandler
    ) {
        $this->resourceContainer = $resourceContainer;
        $this->typeMap = $typeMap;
        $this->relationshipHandler = $relationshipHandler;

        parent::__construct($propertyHandler, $typeHandler);
    }

    /**
     * Copy the properties to an object of the given type.
     *
     * @param mixed[] $properties Associated array of property keys and values.
     * @param string $type The type of the target object.
     * @return object The target object with all properties set.
     */
    public function parseProperties(array $properties, $type)
    {
        if (isset($properties['type']) && isset($properties['id'])) {
            // target type can be overridden via type map
            $type = $this->typeMap->getClass($properties['type']) ?: $type;
        }

        if (ltrim($type, '\\') === \Temando\Shipping\Rest\Response\Fields\Relationship::class) {
            // normalize relationship data to array
            $properties['data'] = [$properties['data']];
        }

        $result = parent::parseProperties($properties, $type);

        if ($result instanceof AbstractResource) {
            $this->resourceContainer->addResource($result);
        }

        return $result;
    }

    /**
     * Convert JSON document into internal types.
     *
     * Relationships are resolved: related resource gets added as property to
     * the parent resource, e.g. resource.relationships.fooRelationship => resource.fooResource
     *
     * @param string $data The data to be parsed
     * @param string $type The type (interface) to map the data to
     * @return mixed The object with populated properties
     */
    public function parse($data, $type)
    {
        $properties = json_decode($data, true);
        $result = $this->parseProperties($properties, $type);

        // iterate over all resources within the response
        foreach ($this->resourceContainer->getResources() as $resource) {
            $this->relationshipHandler->addRelationships($resource, $this->resourceContainer);
        }

        return $result;
    }
}
