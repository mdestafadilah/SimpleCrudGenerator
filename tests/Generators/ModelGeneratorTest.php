<?php

namespace Tests\Generators;

use Tests\TestCase;

class ModelGeneratorTest extends TestCase
{
    /** @test */
    public function it_creates_correct_model_class_content()
    {
        $this->artisan('make:crud', ['name' => $this->model_name, '--no-interaction' => true]);

        $modelPath = app_path($this->model_name.'.php');
        $this->assertFileExists($modelPath);
        $modelClassContent = "<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class {$this->model_name} extends Model
{
    protected \$fillable = ['name', 'description'];

    public function nameLink()
    {
        return link_to_route('{$this->table_name}.show', \$this->name, [\$this->id], [
            'title' => trans(
                'app.show_detail_title',
                ['name' => \$this->name, 'type' => trans('{$this->lang_name}.{$this->lang_name}')]
            ),
        ]);
    }
}
";
        $this->assertEquals($modelClassContent, file_get_contents($modelPath));
    }

    /** @test */
    public function it_creates_correct_namespaced_model_class_content()
    {
        $this->artisan('make:crud', ['name' => 'Entities/References/Category', '--no-interaction' => true]);

        $modelPath = app_path('Entities/References/Category.php');
        $this->assertFileExists($modelPath);
        $modelClassContent = "<?php

namespace App\Entities\References;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected \$fillable = ['name', 'description'];

    public function nameLink()
    {
        return link_to_route('{$this->table_name}.show', \$this->name, [\$this->id], [
            'title' => trans(
                'app.show_detail_title',
                ['name' => \$this->name, 'type' => trans('{$this->lang_name}.{$this->lang_name}')]
            ),
        ]);
    }
}
";
        $this->assertEquals($modelClassContent, file_get_contents($modelPath));
    }
}
