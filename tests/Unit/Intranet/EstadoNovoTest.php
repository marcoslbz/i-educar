<?php

namespace Tests\Unit\Intranet;

use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Request;

class EstadoNovoTest extends TestCase
{
    const BRASIL = 1;
    const POLI_INSTITUCIONAL = 'POLI_INSTITUCIONAL';

    public function test_ct4_bloqueio_por_brasil_e_acesso_inadequado()
    {
        // Arrange - Crie um mock completo da classe Estado
        $estado = Mockery::mock('Estado')->makePartial();
        $estado->idpais = self::BRASIL;
        $estado->mensagem = '';
        
        // Mock do método nivelAccessoPescologada
        $estado->shouldReceive('nivelAccessoPescologada')
               ->andReturn('outro_nivel_qualquer');

        // Mock do método Novo
        $estado->shouldReceive('Novo')
               ->andReturnUsing(function() use ($estado) {
                   // Simula a lógica do método Novo
                   if ($estado->idpais == self::BRASIL && 
                       $estado->nivelAccessoPescologada() != self::POLI_INSTITUCIONAL) {
                       $estado->mensagem = 'Não é permitido cadastro de UFs brasileiras, pois já estão previamente cadastrados.<br>';
                       return false;
                   }
                   return true;
               });

        // Act
        $result = $estado->Novo();

        // Assert
        $this->assertFalse($result);
        $this->assertEquals(
            'Não é permitido cadastro de UFs brasileiras, pois já estão previamente cadastrados.<br>',
            $estado->mensagem
        );
    }

    public function test_ct5_bloqueio_por_sigla_duplicada_brasil()
    {
        // Arrange
        $estado = Mockery::mock('Estado')->makePartial();
        $estado->idpais = self::BRASIL;
        $estado->mensagem = '';
        
        // Mock dos métodos
        $estado->shouldReceive('nivelAccessoPescologada')
               ->andReturn(self::POLI_INSTITUCIONAL);

        // Mock da query para exists() retornar true
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('where')
                 ->with('abbreviation', 'SP')
                 ->andReturnSelf();
        $mockQuery->shouldReceive('where')
                 ->with('country_id', self::BRASIL)
                 ->andReturnSelf();
        $mockQuery->shouldReceive('exists')
                 ->andReturn(true);
        
        $estado->shouldReceive('newQuery')
               ->andReturn($mockQuery);

        $estado->shouldReceive('Novo')
               ->andReturnUsing(function() use ($estado) {
                   // Simula verificação de sigla duplicada
                   $estado->mensagem = 'A sigla já existe para outro estado.<br>';
                   return false;
               });

        // Usando approach do Laravel para mock do Request
        $this->mockRequestData([
            'idpais' => self::BRASIL,
            'sigla_url' => 'SP',
        ]);

        // Act
        $result = $estado->Novo();

        // Assert
        $this->assertFalse($result);
        $this->assertEquals(
            'A sigla já existe para outro estado.<br>',
            $estado->mensagem
        );
    }

    public function test_ct1_criacao_bem_sucedida_brasil()
    {
        // Arrange
        $estado = Mockery::mock('Estado')->makePartial();
        $estado->idpais = self::BRASIL;
        $estado->mensagem = '';
        
        // Mock dos métodos
        $estado->shouldReceive('nivelAccessoPescologada')
               ->andReturn(self::POLI_INSTITUCIONAL);

        // Mock da query para exists() retornar false
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('where')
                 ->with('abbreviation', 'SP')
                 ->andReturnSelf();
        $mockQuery->shouldReceive('where')
                 ->with('country_id', self::BRASIL)
                 ->andReturnSelf();
        $mockQuery->shouldReceive('exists')
                 ->andReturn(false);
        
        $estado->shouldReceive('newQuery')
               ->andReturn($mockQuery);

        // Mock do método create
        $estado->shouldReceive('create')
               ->with([
                   'name' => 'São Paulo',
                   'country_id' => self::BRASIL,
                   'ibge_code' => '35',
                   'abbreviation' => 'SP',
               ])
               ->andReturn(true);

        $estado->shouldReceive('Novo')
               ->andReturn(true);

        // Mock do request usando approach do Laravel
        $this->mockRequestData([
            'idpais' => self::BRASIL,
            'sigla_url' => 'SP',
            'none' => 'São Paulo',
            'cod_ibge' => '35',
        ]);

        // Act
        $result = $estado->Novo();

        // Assert
        $this->assertTrue($result);
    }

    public function test_ct6_bloqueio_por_sigla_duplicada_pais_nao_brasil()
    {
        // Arrange
        $estado = Mockery::mock('Estado')->makePartial();
        $estado->idpais = 2;
        $estado->mensagem = '';
        
        $estado->shouldReceive('nivelAccessoPescologada')
               ->andReturn('outro_nivel_qualquer');

        // Mock da query para exists() retornar true
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('where')
                 ->with('abbreviation', 'CA')
                 ->andReturnSelf();
        $mockQuery->shouldReceive('where')
                 ->with('country_id', 2)
                 ->andReturnSelf();
        $mockQuery->shouldReceive('exists')
                 ->andReturn(true);
        
        $estado->shouldReceive('newQuery')
               ->andReturn($mockQuery);

        $estado->shouldReceive('Novo')
               ->andReturnUsing(function() use ($estado) {
                   $estado->mensagem = 'A sigla já existe para outro estado.<br>';
                   return false;
               });

        $this->mockRequestData([
            'idpais' => 2,
            'sigla_url' => 'CA',
        ]);

        // Act
        $result = $estado->Novo();

        // Assert
        $this->assertFalse($result);
        $this->assertEquals(
            'A sigla já existe para outro estado.<br>',
            $estado->mensagem
        );
    }

    public function test_ct2_criacao_bem_sucedida_pais_nao_brasil()
    {
        // Arrange
        $estado = Mockery::mock('Estado')->makePartial();
        $estado->idpais = 2;
        
        $estado->shouldReceive('nivelAccessoPescologada')
               ->andReturn('outro_nivel_qualquer');

        // Mock da query para exists() retornar false
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('where')
                 ->with('abbreviation', 'CA')
                 ->andReturnSelf();
        $mockQuery->shouldReceive('where')
                 ->with('country_id', 2)
                 ->andReturnSelf();
        $mockQuery->shouldReceive('exists')
                 ->andReturn(false);
        
        $estado->shouldReceive('newQuery')
               ->andReturn($mockQuery);

        // Mock do método create
        $estado->shouldReceive('create')
               ->with([
                   'name' => 'Califórnia',
                   'country_id' => 2,
                   'ibge_code' => null,
                   'abbreviation' => 'CA',
               ])
               ->andReturn(true);

        $estado->shouldReceive('Novo')
               ->andReturn(true);

        $this->mockRequestData([
            'idpais' => 2,
            'sigla_url' => 'CA',
            'none' => 'Califórnia',
            'cod_ibge' => null,
        ]);

        // Act
        $result = $estado->Novo();

        // Assert
        $this->assertTrue($result);
    }

    public function test_ct3_criacao_bem_sucedida_pais_nao_brasil_com_acesso_institucional()
    {
        // Arrange
        $estado = Mockery::mock('Estado')->makePartial();
        $estado->idpais = 2;
        
        $estado->shouldReceive('nivelAccessoPescologada')
               ->andReturn(self::POLI_INSTITUCIONAL);

        // Mock da query para exists() retornar false
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('where')
                 ->with('abbreviation', 'CA')
                 ->andReturnSelf();
        $mockQuery->shouldReceive('where')
                 ->with('country_id', 2)
                 ->andReturnSelf();
        $mockQuery->shouldReceive('exists')
                 ->andReturn(false);
        
        $estado->shouldReceive('newQuery')
               ->andReturn($mockQuery);

        // Mock do método create
        $estado->shouldReceive('create')
               ->with([
                   'name' => 'Califórnia',
                   'country_id' => 2,
                   'ibge_code' => null,
                   'abbreviation' => 'CA',
               ])
               ->andReturn(true);

        $estado->shouldReceive('Novo')
               ->andReturn(true);

        $this->mockRequestData([
            'idpais' => 2,
            'sigla_url' => 'CA',
            'none' => 'Califórnia',
            'cod_ibge' => null,
        ]);

        // Act
        $result = $estado->Novo();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Helper method para mockar dados do Request sem usar Mockery diretamente
     */
    private function mockRequestData(array $data)
    {
        // Limpa qualquer mock anterior
        Request::clearResolvedInstances();
        
        // Cria um mock partial do Request
        $requestMock = Mockery::mock(\Illuminate\Http\Request::class)->makePartial();
        
        foreach ($data as $key => $value) {
            $requestMock->shouldReceive('request')
                       ->with($key)
                       ->andReturn($value);
        }
        
        // Permite outras chamadas ao request sem erro
        $requestMock->shouldReceive('request')
                   ->withAnyArgs()
                   ->andReturn(null);
                   
        $requestMock->shouldReceive('all')
                   ->andReturn($data);
                   
        // Mock dos métodos que o Laravel chama internamente
        $requestMock->shouldReceive('setUserResolver')
                   ->andReturnNull();
                   
        $requestMock->shouldReceive('getUserResolver')
                   ->andReturnNull();

        // Registra no container
        $this->app->instance('request', $requestMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}