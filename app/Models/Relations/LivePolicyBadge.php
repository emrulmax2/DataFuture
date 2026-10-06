<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * PolicyAssignment::badge() — the live badge the assignment's employee holds
 * for its policy at its level.
 *
 * A badge is keyed on three columns (employee, policy, level), not on the
 * assignment: it outlives an archived assignment, and a re-created
 * assignment for the same policy and level shares it. Eloquent's HasOne only
 * matches one column, so this narrows the lazy query, the eager load and
 * whereHas()/withCount() to all three. Revoked (soft-deleted) badges are left
 * out by PolicyBadge's SoftDeletes scope.
 */
class LivePolicyBadge extends HasOne
{
    public function __construct(Builder $query, Model $parent)
    {
        parent::__construct($query, $parent, $query->getModel()->qualifyColumn('employee_id'), 'employee_id');
    }

    public function addConstraints()
    {
        if(static::$constraints):
            $related = $this->related;
            $this->getRelationQuery()
                ->where($related->qualifyColumn('employee_id'), '=', $this->parent->getAttribute('employee_id'))
                ->where($related->qualifyColumn('policy_document_id'), '=', $this->parent->getAttribute('policy_document_id'))
                ->where($related->qualifyColumn('level'), '=', $this->parent->getAttribute('level'))
                ->orderBy($related->qualifyColumn('id'), 'DESC');
        endif;
    }

    public function addEagerConstraints(array $models)
    {
        $related = $this->related;
        $this->getRelationQuery()
            ->whereIn($related->qualifyColumn('employee_id'), $this->getKeys($models, 'employee_id'))
            ->whereIn($related->qualifyColumn('policy_document_id'), $this->getKeys($models, 'policy_document_id'));
    }

    public function match(array $models, Collection $results, $relation)
    {
        /* Newest live badge per (employee, policy, level). */
        $dictionary = [];
        foreach($results as $badge):
            $key = $this->compositeKey($badge);
            if(!isset($dictionary[$key]) || $badge->getKey() > $dictionary[$key]->getKey()):
                $dictionary[$key] = $badge;
            endif;
        endforeach;

        foreach($models as $model):
            $key = $this->compositeKey($model);
            $model->setRelation($relation, (isset($dictionary[$key]) ? $dictionary[$key] : $this->getDefaultFor($model)));
        endforeach;

        return $models;
    }

    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        return parent::getRelationExistenceQuery($query, $parentQuery, $columns)
            ->whereColumn($this->parent->qualifyColumn('policy_document_id'), '=', $query->getModel()->qualifyColumn('policy_document_id'))
            ->whereColumn($this->parent->qualifyColumn('level'), '=', $query->getModel()->qualifyColumn('level'));
    }

    protected function setForeignAttributesForCreate(Model $model)
    {
        parent::setForeignAttributesForCreate($model);
        $model->setAttribute('policy_document_id', $this->parent->getAttribute('policy_document_id'));
        $model->setAttribute('level', $this->parent->getAttribute('level'));
    }

    protected function compositeKey(Model $model): string
    {
        return (int) $model->getAttribute('employee_id').'|'.(int) $model->getAttribute('policy_document_id').'|'.(string) $model->getAttribute('level');
    }
}
