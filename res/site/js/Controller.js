class Controller{

    constructor(){
        this.subAll();
        
    }

    subAll() {
        
        console.log('ok');
        let subAll = $('.product-subtotal > span').html();

        let v = $('.product-subtotal > span').text().split(" ").filter(p => p != "");

        var array = v;

            var soma = 0;

            for (var x=0; x < array.length; x++) {

           soma += parseFloat(array[x]);
         }

         $('.cart-subtotal > td > .amount').html(`$ ${soma.toFixed(2)}`);

         $('.order-total > td > strong > .amount').html(`$ ${soma.toFixed(2)}`);

         this.freight(soma);


    }

    freight(subTotal){

        document.querySelector("#submit-freight").addEventListener('click', e=>{
            e.preventDefault();
            let freight = $('#cep').val();
            this.loadFreight(true);
            
        $.ajax({
            url: 'http://www.megaclick.com.br/cart/freight',
            method: 'POST',
            data: {zipcode: freight},
            datatype: 'json',
        }).done(function(result){
            
            let json = JSON.parse(result);
            let freightPrice = json.Servicos.cServico.Valor;
            let freighyDay = json.Servicos.cServico.PrazoEntrega;

            let freightPriceUpdated = freightPrice.replace(",", ".");

            const td = `
                <th>Frete</th>
                <td>$ ${freightPriceUpdated} <small>prazo de ${freighyDay} dia(s)</small></td>
            `;
            $('.shipping').html(td);
            
            let total = parseFloat(freightPriceUpdated) + parseFloat(subTotal);
            console.log(total);
            $('.order-total > td > strong > .amount').html(`$ ${total}`);
            
        }).then(()=>{
            console.log('aqui ok');
            this.loadFreight(false);
        });

        });

    } 

    loadFreight(start){ 
        if(start){
            let div = document.createElement('div');
            div.className = 'lds-dual-ring';
    
            $('.cross-sells').append(div);
        }else{
            $('.lds-dual-ring').removeClass();
        }
        

    }

 

}