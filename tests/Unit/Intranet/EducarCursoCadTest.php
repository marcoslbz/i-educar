<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Controllers\EducarCursoCadController; // Ajuste conforme o namespace

class EducarCursoCadControllerTest extends TestCase
{
    public function testEtapasIgualZero()
    {
        $controller = new EducarCursoCadController();
        $_POST['qtd_etapas'] = 0;
        $_POST['nm_curso'] = 'Curso Teste';
        $_POST['sgl_curso'] = 'CT';
        
        $result = $controller->Novo();
        
        $this->assertFalse($result);
        $this->assertStringContainsString('A quantidade de etapas deve ser maior que zero', $controller->mensagem);
    }
    
    public function testEtapasIgualNegativo()
    {
        $controller = new EducarCursoCadController();
        $_POST['qtd_etapas'] = -5;
        $_POST['nm_curso'] = 'Curso Teste';
        $_POST['sgl_curso'] = 'CT';
        
        $result = $controller->Novo();
        
        $this->assertFalse($result);
        $this->assertStringContainsString('A quantidade de etapas deve ser maior que zero', $controller->mensagem);
    }
    
    public function testEtapasIgualPositivo()
    {
        $controller = new EducarCursoCadController();
        $_POST['qtd_etapas'] = 6;
        $_POST['nm_curso'] = 'Curso Teste';
        $_POST['sgl_curso'] = 'CT';
        $_POST['ref_cod_nivel_ensino'] = 1;
        $_POST['ref_cod_tipo_ensino'] = 1;
        
        // Mock do método cadastra para simular sucesso
        $mock = $this->createMock(clsPmieducarCurso::class);
        $mock->method('cadastra')->willReturn(123);
        
        $result = $controller->Novo();
        
        $this->assertNotFalse($result);
        $this->assertStringNotContainsString('A quantidade de etapas deve ser maior que zero', $controller->mensagem);
    }
}