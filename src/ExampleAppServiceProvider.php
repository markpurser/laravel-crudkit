<?php

namespace Markpurser\LaravelCrudKit;

use Illuminate\Support\ServiceProvider;

use Markpurser\LaravelCrudKit\AdminPanelController;
use Markpurser\LaravelCrudKit\AdminPanel\PageStore;
use Markpurser\LaravelCrudKit\AdminPanel\TableDescriptor;
use Markpurser\LaravelCrudKit\AdminPanel\PageDescriptor;

use DB;

class ExampleAppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Enable Sqlite foreign key constraints
        if(config('database.default') == 'sqlite'){
            $db = app()->make('db');
            $db->connection()->getPdo()->exec("pragma foreign_keys=1");
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->resolving(AdminPanelController::class, function ($adminPanel, $app) {

            // Table schema
            $authorTable = (new TableDescriptor('author', 'Author'))
                ->addColumn('firstname', 'First Name', 'string', ['required' => true, 'max' => 255])
                ->addColumn('lastname', 'Last Name', 'string', ['required' => true, 'max' => 255])
                ->addColumn('active', 'Active', 'boolean')
                ->addTimestamps()
                ->addSoftDelete();

            $titleTable = (new TableDescriptor('title', 'Title'))
                ->addColumn('title', 'Title', 'string', ['required' => true, 'max' => 255])
                ->addColumn('description', 'Description', 'textarea', ['max' => 65535])
                ->addColumn('price', 'Price', 'price', ['required' => true])
                ->addColumn('genre', 'Genre', 'enum', ['required' => true, 'enum' => ['Suspense', 'Crime', 'Romance', 'Computing', 'Horror']])
                ->addColumn('tag', 'Tag', 'editabledropdown')
                ->addColumn('rating', 'Rating', 'number')
                ->addColumn('pub_date', 'Publish Date', 'datetime', ['required' => true]);

            $publisherTable = (new TableDescriptor('publisher', 'Publisher'))
                ->addColumn('name', 'Name', 'string', ['required' => true, 'max' => 255])
                ->addColumn('city', 'City', 'string', ['max' => 255])
                ->addColumn('email', 'Email', 'email');

            // Relationships
            $titleTable
                ->addManyToOne($publisherTable, 'pub_id', 'name', ['required' => true, 'tip' => 'Add a publisher of the title'])
                ->addManyToMany($authorTable, "lastname", "author_title", "title_id", "author_id");

            $authorTable
                ->addManyToMany($titleTable, "title", "author_title", "author_id", "title_id");


            // Pages
            $pageStore = new PageStore([

                (new PageDescriptor('Author'))
                    ->addTable($authorTable)
                    ->setSummaryColumns(['firstname', 'lastname', 'active'])
                    ->setInitialValuesCallback(function ($columns) {

                        $columns['active']->data = 1;

                        return $columns;
                    }),

                (new PageDescriptor('Title'))
                    ->addTable($titleTable)
                    ->setSummaryColumns(['title', 'genre', 'tag', 'rating', 'price', 'pub_date'])
                    ->setInitialValuesCallback(function ($columns) {

                        $result = DB::table('title')->select('tag')->distinct()->get();
                        $tags = array_pluck($result, 'tag');

                        $columns['tag']->options['dropdownlist'] = $tags;

                        $columns['price']->data = 0;
                        $columns['rating']->data = 0;
                        $columns['pub_date']->data = date("Y-m-d H:i:s");

                        return $columns;
                    }),

                (new PageDescriptor('Publisher'))
                    ->addTable($publisherTable)
                    ->setSummaryColumns(['name', 'email']),

            ]);


            $adminPanel->setPageStore($pageStore);
        });
    }
}
