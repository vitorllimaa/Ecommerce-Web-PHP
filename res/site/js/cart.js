

$(document).ready(function(){

    $('#submit-freight').on('click', function(e){
        console.log('teste');
        e.preventDefault();
        let freight = $('#cep').val();
        

    $.ajax({
        url: 'http://www.megaclick.com.br/cart/freight',
        method: 'POST',
        data: {zipcode: freight},
        datatype: 'json',
    }).done(function(result){
        let json = JSON.parse(result);
        let freightPrice = json.Servicos.cServico.Valor;
        let freighyDay = json.Servicos.cServico.PrazoEntrega;

        /* let freightPrice = dfreightPrice.repalce(",", ".");
        let freighyDay = dfreighyDay.repalce(",", "."); */

        console.log(freightPrice, freighyDay);

        const td = `
            <th>Frete</th>
            <td>$${freightPrice} <small>prazo de ${freighyDay} dia(s)</small></td>
        `;
        $('.shipping').html(td); 
       
        
     });

   });
});