# https://github.com/MinKruger/TravelingSalesman

$tamanho1 = 5
$tamanho2 = 6
$tamanho3 = 24

def preenche_caminho(matriz)
    matriz2 = [
                [0, 1, 2, 3, 4, 0], 
                [0, 1, 2, 4, 3, 0], 
                [0, 1, 3, 2, 4, 0], 
                [0, 1, 3, 4, 2, 0], 
                [0, 1, 4, 2, 3, 0],
                [0, 1, 4, 3, 2, 0],
                [0, 2, 1, 3, 4, 0],
                [0, 2, 1, 4, 3, 0],
                [0, 2, 3, 1, 4, 0],
                [0, 2, 3, 4, 1, 0],
                [0, 2, 4, 1, 3, 0],
                [0, 2, 4, 3, 1, 0],
                [0, 3, 1, 2, 4, 0],
                [0, 3, 1, 4, 2, 0],
                [0, 3, 2, 1, 4, 0],
                [0, 3, 2, 4, 1, 0],
                [0, 3, 4, 1, 2, 0],
                [0, 3, 4, 2, 1, 0],
                [0, 4, 1, 2, 3, 0],
                [0, 4, 1, 3, 2, 0],
                [0, 4, 2, 1, 3, 0],
                [0, 4, 2, 3, 1, 0],
                [0, 4, 3, 1, 2, 0],
                [0, 4, 3, 2, 1, 0]
            ]
    
    matriz2.each_with_index do |row, row_index|
        #puts "Working on row: #{row_index + 1}"
        row.each_with_index do |col, col_index|
            #puts "Working on cell: (#{col_index + 1},#{row_index + 1}) = #{col}"
            matriz[row_index][col_index] = matriz2[row_index][col_index]
        end
    end
end

def exibir_24x6(matriz)
    matriz.each_with_index do |row, row_index|
        print "Caminho(#{row_index + 1}): "
        row.each_with_index do |col, col_index|
            print "\t#{matriz[row_index][col_index] + 1}"
        end
        print "\n"
    end
end

def preenche_custo(matriz)
    iaux = 0
    flag = 1
    flag1 = 1
    cont = 0
    sorteados = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
    matriz.each_with_index do |row, row_index|
        row.each_with_index do |col, col_index|
            if row_index <= col_index
                if row_index == col_index
                    matriz[row_index][col_index] = 0;
                else
                    if cont > 0
                        escolhido = Random.rand(1...30)
                        matriz[row_index][col_index] = escolhido
                        sorteados[iaux] = escolhido
                        cont = 1
                    else
                        while flag1 != 0
                            escolhido = Random.rand(1...30)
                            iaux = 0
                            flag = 1
                            while iaux < cont && flag == 1
                                if sorteados[iaux] == escolhido
                                    flag = 0
                                end
                                iaux += 1
                            end
                            if flag != 0
                                flag1 = 0
                            end
                        end
                        matriz[row_index][col_index] = escolhido
                        sorteados[cont] = escolhido
                        cont += 1
                    end
                end
            else
                matriz[row_index][col_index] = matriz[col_index][row_index]
            end
        end
    end
end 

def exibir_5x5(matriz)
    print "\tC1\tC2\tC3\tC4\tC5\n"
    matriz.each_with_index do |row, row_index|
        row.each_with_index do |col, col_index|
            if col_index == 0
                print "C#{row_index + 1}"
            end
            print "\t#{matriz[row_index][col_index]}"
        end
        print "\n"
    end
end

def caminho_otimo(vetor)
    i = 1
    melhor = vetor[0]
    iMelhor = 0
    for i in ($tamanho3).step(1) do
        if vetor[i] < melhor
            melhor = vetor[i]
            iMelhor = i
        end
    end
    return iMelhor
end

def solucao_gulosa(matriz_custo, caminho_guloso)
    i = 1
    candidatos = [2,3,4,5]
    melhor_candidato = 0
    caminho_guloso[0] = 1
    while tem_candidatos(candidatos) > 0
        melhor_indice = menor_caminho(matriz_custo, candidatos, melhor_candidato)
        caminho_guloso[i] = candidatos[melhor_indice]
        melhor_candidato = candidatos[melhor_indice] - 1
        candidatos[melhor_indice] = -1
        i += 1
    end
end

def tem_candidatos(candidatos)
    quant = 0
    i = 0
    while i < ($tamanho1 - 1)
        if candidatos[i] > 0
            quant += 1
        end
        i += 1
    end
    return quant
end

def menor_caminho(matriz_custo, candidatos, origem)
    menor = 1000
    menor_indice = 0
    j = 0
    while j < ($tamanho1 - 1)
        aux = candidatos[j]
        if aux > 0
            if matriz_custo[origem][aux-1] < menor
                menor = matriz_custo[origem][aux-1]
                menor_indice = j
            end
        end
        j += 1
    end
    return menor_indice
end

def indice_caminho_guloso(caminhos, caminho_guloso)
    i = 0
    j = 0
    achou_linha = 1
    achou_coluna = 1
    indice = 0
    while (i < $tamanho3) && achou_linha
        achou_coluna = 1
        j = 0
        while (j < $tamanho2 - 1) && achou_coluna
            if caminhos[i][j] != caminho_guloso[j] - 1
                achou_coluna = 0
            end
            j += 1
        end
        if achou_coluna == 1
            indice = i
            achou_linha = 0
        end
        i += 1
    end
    return indice
end

def main()
    caminhos = Array.new($tamanho3){ Array.new($tamanho2) }
    preenche_caminho(caminhos)
    custo = Array.new($tamanho1){ Array.new($tamanho1) }
    preenche_custo(custo)
    exibir_24x6(caminhos)
    puts "\n\n"
    exibir_5x5(custo)
    puts "\n\n"
    percurso = Array.new($tamanho3) { 0 }
    (0...$tamanho3).each do |row|
        (0...$tamanho2 - 1).each do |cell|
            percurso[row] = percurso[row] + custo[caminhos[row][cell]][caminhos[row][cell+1]]
        end
    end
    melhor_caminho = caminho_otimo(percurso);
    puts "*******SOLUCAO OTIMA*********\n\n"
    print "Percurso: ["
    (0...$tamanho3).each do |row|
        if row == melhor_caminho
            print " {#{percurso[row]}}"
        else
            print " #{percurso[row]}"
        end
    end
    puts " ]\n\n"
    puts "Caminho Otimo: #{melhor_caminho + 1}\n"
    print "Caminho Otimo: [ "
    (0...$tamanho2).each do |cell|
        print "C#{caminhos[melhor_caminho][cell] + 1}-"
    end
    puts " \b\b ]\n\n"
    puts "\n\n"
    puts "*******SOLUCAO GULOSA*********\n\n"
    caminho_guloso = Array.new($tamanho1) { 0 }
    solucao_gulosa(custo, caminho_guloso);
    melhor_caminho = indice_caminho_guloso(caminhos, caminho_guloso);
    print "Percurso: ["
    (0...$tamanho3).each do |row|
        if row == melhor_caminho
            print " {#{percurso[row]}}"
        else
            print " #{percurso[row]}"
        end
    end
    puts " ]\n\n"
    puts "Caminho Guloso: #{melhor_caminho + 1}\n"
    print "Caminho Guloso: [ "
    (0...$tamanho1).each do |cell|
        print "C#{caminho_guloso[cell]}-"
    end
    puts "C1]\n\n"
end

main()
