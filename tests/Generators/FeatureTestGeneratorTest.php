<?php

namespace Tests\Generators;

use Tests\TestCase;

class FeatureTestGeneratorTest extends TestCase
{
    /** @test */
    public function it_creates_browser_kit_base_test_class()
    {
        $this->artisan('make:crud', ['name' => $this->model_name, '--no-interaction' => true]);

        $this->assertFileExists(base_path("tests/BrowserKitTest.php"));
        $browserKitTestClassContent = "<?php

namespace Tests;

use App\User;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

abstract class BrowserKitTest extends BaseTestCase
{
    use CreatesApplication;

    protected \$baseUrl = 'http://localhost';

    protected function setUp()
    {
        parent::setUp();
        \Hash::setRounds(5);
    }

    protected function loginAsUser()
    {
        \$user = factory(User::class)->create();
        \$this->actingAs(\$user);

        return \$user;
    }
}
";
        $this->assertEquals($browserKitTestClassContent, file_get_contents(base_path("tests/BrowserKitTest.php")));
    }

    /** @test */
    public function it_creates_correct_feature_test_class_content()
    {
        $this->artisan('make:crud', ['name' => $this->model_name, '--no-interaction' => true]);

        $this->assertFileExists(base_path("tests/Feature/Manage{$this->plural_model_name}Test.php"));
        $modelClassContent = "<?php

namespace Tests\Feature;

use {$this->full_model_name};
use Tests\BrowserKitTest as TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class Manage{$this->plural_model_name}Test extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_see_{$this->lang_name}_list_in_{$this->lang_name}_index_page()
    {
        \${$this->single_model_var_name}1 = factory({$this->model_name}::class)->create(['name' => 'Testing name', 'description' => 'Testing 123']);
        \${$this->single_model_var_name}2 = factory({$this->model_name}::class)->create(['name' => 'Testing name', 'description' => 'Testing 456']);

        \$this->loginAsUser();
        \$this->visit(route('{$this->table_name}.index'));
        \$this->see(\${$this->single_model_var_name}1->name);
        \$this->see(\${$this->single_model_var_name}2->name);
    }

    /** @test */
    public function user_can_create_a_{$this->lang_name}()
    {
        \$this->loginAsUser();
        \$this->visit(route('{$this->table_name}.index'));

        \$this->click(trans('{$this->lang_name}.create'));
        \$this->seePageIs(route('{$this->table_name}.index', ['action' => 'create']));

        \$this->type('{$this->model_name} 1 name', 'name');
        \$this->type('{$this->model_name} 1 description', 'description');
        \$this->press(trans('{$this->lang_name}.create'));

        \$this->seePageIs(route('{$this->table_name}.index'));

        \$this->seeInDatabase('{$this->table_name}', [
            'name'   => '{$this->model_name} 1 name',
            'description'   => '{$this->model_name} 1 description',
        ]);
    }

    /** @test */
    public function user_can_edit_a_{$this->lang_name}_within_search_query()
    {
        \$this->loginAsUser();
        \${$this->single_model_var_name} = factory({$this->model_name}::class)->create(['name' => 'Testing 123']);

        \$this->visit(route('{$this->table_name}.index', ['q' => '123']));
        \$this->click('edit-{$this->single_model_var_name}-'.\${$this->single_model_var_name}->id);
        \$this->seePageIs(route('{$this->table_name}.index', ['action' => 'edit', 'id' => \${$this->single_model_var_name}->id, 'q' => '123']));

        \$this->type('{$this->model_name} 1 name', 'name');
        \$this->type('{$this->model_name} 1 description', 'description');
        \$this->press(trans('{$this->lang_name}.update'));

        \$this->seePageIs(route('{$this->table_name}.index', ['q' => '123']));

        \$this->seeInDatabase('{$this->table_name}', [
            'name'   => '{$this->model_name} 1 name',
            'description'   => '{$this->model_name} 1 description',
        ]);
    }

    /** @test */
    public function user_can_delete_a_{$this->lang_name}()
    {
        \$this->loginAsUser();
        \${$this->single_model_var_name} = factory({$this->model_name}::class)->create();

        \$this->visit(route('{$this->table_name}.index', [\${$this->single_model_var_name}->id]));
        \$this->click('del-{$this->single_model_var_name}-'.\${$this->single_model_var_name}->id);
        \$this->seePageIs(route('{$this->table_name}.index', ['action' => 'delete', 'id' => \${$this->single_model_var_name}->id]));

        \$this->seeInDatabase('{$this->table_name}', [
            'id' => \${$this->single_model_var_name}->id,
        ]);

        \$this->press(trans('app.delete_confirm_button'));

        \$this->dontSeeInDatabase('{$this->table_name}', [
            'id' => \${$this->single_model_var_name}->id,
        ]);
    }
}
";
        $this->assertEquals($modelClassContent, file_get_contents(base_path("tests/Feature/Manage{$this->plural_model_name}Test.php")));
    }

    /** @test */
    public function it_generates_base_test_class_based_on_config_file()
    {
        config(['simple-crud.base_test_path' => 'tests/TestCase.php']);
        config(['simple-crud.base_test_class' => 'Tests\TestCase']);

        $baseTestPath  = base_path('tests/TestCase.php');
        $baseTestClass = 'TestCase';

        $this->artisan('make:crud', ['name' => $this->model_name, '--no-interaction' => true]);

        $this->assertFileExists($baseTestPath);
        $browserKitTestClassContent = "<?php

namespace Tests;

use App\User;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

abstract class {$baseTestClass} extends BaseTestCase
{
    use CreatesApplication;

    protected \$baseUrl = 'http://localhost';

    protected function setUp()
    {
        parent::setUp();
        \Hash::setRounds(5);
    }

    protected function loginAsUser()
    {
        \$user = factory(User::class)->create();
        \$this->actingAs(\$user);

        return \$user;
    }
}
";
        $this->assertEquals($browserKitTestClassContent, file_get_contents($baseTestPath));
    }

    /** @test */
    public function it_creates_correct_feature_test_class_with_base_test_class_based_on_config_file()
    {
        config(['simple-crud.base_test_path' => 'tests/TestCase.php']);
        config(['simple-crud.base_test_class' => 'Tests\TestCase']);

        $this->artisan('make:crud', ['name' => $this->model_name, '--no-interaction' => true]);

        $this->assertFileExists(base_path("tests/Feature/Manage{$this->plural_model_name}Test.php"));
        $modelClassContent = "<?php

namespace Tests\Feature;

use {$this->full_model_name};
use Tests\TestCase as TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class Manage{$this->plural_model_name}Test extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_see_{$this->lang_name}_list_in_{$this->lang_name}_index_page()
    {
        \${$this->single_model_var_name}1 = factory({$this->model_name}::class)->create(['name' => 'Testing name', 'description' => 'Testing 123']);
        \${$this->single_model_var_name}2 = factory({$this->model_name}::class)->create(['name' => 'Testing name', 'description' => 'Testing 456']);

        \$this->loginAsUser();
        \$this->visit(route('{$this->table_name}.index'));
        \$this->see(\${$this->single_model_var_name}1->name);
        \$this->see(\${$this->single_model_var_name}2->name);
    }

    /** @test */
    public function user_can_create_a_{$this->lang_name}()
    {
        \$this->loginAsUser();
        \$this->visit(route('{$this->table_name}.index'));

        \$this->click(trans('{$this->lang_name}.create'));
        \$this->seePageIs(route('{$this->table_name}.index', ['action' => 'create']));

        \$this->type('{$this->model_name} 1 name', 'name');
        \$this->type('{$this->model_name} 1 description', 'description');
        \$this->press(trans('{$this->lang_name}.create'));

        \$this->seePageIs(route('{$this->table_name}.index'));

        \$this->seeInDatabase('{$this->table_name}', [
            'name'   => '{$this->model_name} 1 name',
            'description'   => '{$this->model_name} 1 description',
        ]);
    }

    /** @test */
    public function user_can_edit_a_{$this->lang_name}_within_search_query()
    {
        \$this->loginAsUser();
        \${$this->single_model_var_name} = factory({$this->model_name}::class)->create(['name' => 'Testing 123']);

        \$this->visit(route('{$this->table_name}.index', ['q' => '123']));
        \$this->click('edit-{$this->single_model_var_name}-'.\${$this->single_model_var_name}->id);
        \$this->seePageIs(route('{$this->table_name}.index', ['action' => 'edit', 'id' => \${$this->single_model_var_name}->id, 'q' => '123']));

        \$this->type('{$this->model_name} 1 name', 'name');
        \$this->type('{$this->model_name} 1 description', 'description');
        \$this->press(trans('{$this->lang_name}.update'));

        \$this->seePageIs(route('{$this->table_name}.index', ['q' => '123']));

        \$this->seeInDatabase('{$this->table_name}', [
            'name'   => '{$this->model_name} 1 name',
            'description'   => '{$this->model_name} 1 description',
        ]);
    }

    /** @test */
    public function user_can_delete_a_{$this->lang_name}()
    {
        \$this->loginAsUser();
        \${$this->single_model_var_name} = factory({$this->model_name}::class)->create();

        \$this->visit(route('{$this->table_name}.index', [\${$this->single_model_var_name}->id]));
        \$this->click('del-{$this->single_model_var_name}-'.\${$this->single_model_var_name}->id);
        \$this->seePageIs(route('{$this->table_name}.index', ['action' => 'delete', 'id' => \${$this->single_model_var_name}->id]));

        \$this->seeInDatabase('{$this->table_name}', [
            'id' => \${$this->single_model_var_name}->id,
        ]);

        \$this->press(trans('app.delete_confirm_button'));

        \$this->dontSeeInDatabase('{$this->table_name}', [
            'id' => \${$this->single_model_var_name}->id,
        ]);
    }
}
";
        $this->assertEquals($modelClassContent, file_get_contents(base_path("tests/Feature/Manage{$this->plural_model_name}Test.php")));
    }
}
