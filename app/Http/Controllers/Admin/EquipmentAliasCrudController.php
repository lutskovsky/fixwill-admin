<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EquipmentAliasRequest;
use App\Models\EquipmentAlias;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class EquipmentAliasCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class EquipmentAliasCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(EquipmentAlias::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/equipment-alias');
        CRUD::setEntityNameStrings('замена типов оборудования', 'замены типов оборудования');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // set columns from db columns.

        CRUD::column('target')
            ->label('Исходное название');
        CRUD::column('alias')
            ->label('Заменить на');
        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(EquipmentAliasRequest::class);

        CRUD::field('target')
            ->label('Исходное название');
        CRUD::field('alias')
            ->label('Заменить на');

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }
}
