<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;

/**
 * Metric filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFilter implements AttributeFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the filter
     */
    public function __construct()
    {
        $this->supportedOperators = ['<', '<=', '=', '>=', '>', 'EMPTY'];
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->qb = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AbstractAttribute $attribute)
    {
        return $attribute->getAttributeType() === 'pim_catalog_metric';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator)
    {
        return in_array($operator, $this->supportedOperators);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperators()
    {
        return $this->supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value, $context = [])
    {
        $data = (float) $value;

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $context);
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
        $fieldData = sprintf('%s.baseData', $field);

        switch ($operator) {
            case '<':
                $this->qb->field($fieldData)->lt($data);
                break;
            case '<=':
                $this->qb->field($fieldData)->lte($data);
                break;
            case '>':
                $this->qb->field($fieldData)->gt($data);
                break;
            case '>=':
                $this->qb->field($fieldData)->gte($data);
                break;
            case 'EMPTY':
                $this->qb->field($fieldData)->equals(null);
                break;
            default:
                $this->qb->field($fieldData)->equals($data);
        }

        return $this;
    }
}
