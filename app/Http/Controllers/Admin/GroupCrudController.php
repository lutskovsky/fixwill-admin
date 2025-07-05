<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\GroupRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class GroupCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class GroupCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Group::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/group');
        CRUD::setEntityNameStrings('group', 'groups');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->addColumns([
            [
                'name' => 'name',
                'label' => trans('backpack::permissionmanager.name'),
                'type' => 'text',
            ],
            [  // Select
                'label'     => "Канал WhatsApp",
                'type'      => 'select',
                'name'      => 'whatsapp_channel_id', // the db column for the foreign key
                'entity'    => 'whatsappChannel',
                'model'     => "App\Models\Channel", // related model
                'attribute' => 'name', // foreign key attribute that is shown to user
            ],
            [  // Select
                'label'     => "Канал SMS",
                'type'      => 'select',
                'name'      => 'sms_channel_id', // the db column for the foreign key
                'entity'    => 'smsChannel',
                'model'     => "App\Models\Channel", // related model
                'attribute' => 'name', // foreign key attribute that is shown to user
            ],
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(GroupRequest::class);
        $this->crud->addFields([
            [
                'name' => 'name',
                'label' => trans('backpack::permissionmanager.name'),
                'type' => 'text',
            ],

            [
                'label'     => "Канал WhatsApp",
                'type'      => 'select',
                'name'      => 'whatsapp_channel_id', // the db column for the foreign key
                'model'     => "App\Models\Channel", // related model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'options'   => (function ($query) {
                    return $query->where('type', 'whatsapp')->get();
                }),
            ],
            [
                'label'     => "Канал SMS",
                'type'      => 'select',
                'name'      => 'sms_channel_id', // the db column for the foreign key
                'model'     => "App\Models\Channel", // related model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'options'   => (function ($query) {
                    return $query->where('type', 'sms')->get();
                }),
            ],
            [
                'label'     => "ТГ чат",
                'type'      => 'text',
                'name'      => 'tg_chat_id',
            ],
        ]);

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
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
}
