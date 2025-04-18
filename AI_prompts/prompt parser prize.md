Есть фрагмент html:
 <div id="awardRowList" class="row-list award-59">
                        <div class="top"></div>
                        <div class="row header">
                          <div class="col">Place</div>
                          <div class="col">Prize</div>
                          <div class="col" style="display: none; margin-left: 12px;">Seat1</div>
                        </div>
                        <div class="row data">
                          <div class="col">1</div>
                          <div class="col"><span class="match-reward"
                              style="display: inline-flex; align-items: center;"><img
                                src="./1500_finish_files/ISqjh0q7.png" style="margin-right: 4px;"><span
                                class="asset-amount-yellow asset-amount-yellow-token"
                                data-text="1,866.66">1,866.66</span></span></div>
                          <div class="col" style="display: none;"><span class="match-reward">1 Seat</span></div>
                        </div>
                        <div class="row data">
                          <div class="col">2</div>
                          <div class="col"><span class="match-reward"
                              style="display: inline-flex; align-items: center;"><img
                                src="./1500_finish_files/ISqjh0q7.png" style="margin-right: 4px;"><span
                                class="asset-amount-yellow asset-amount-yellow-token"
                                data-text="1,466.66">1,466.66</span></span></div>
                          <div class="col" style="display: none;"><span class="match-reward">1 Seat</span></div>
                        </div>
                        <div class="row data">
                          <div class="col">3</div>
                          <div class="col"><span class="match-reward"
                              style="display: inline-flex; align-items: center;"><img
                                src="./1500_finish_files/ISqjh0q7.png" style="margin-right: 4px;"><span
                                class="asset-amount-yellow asset-amount-yellow-token"
                                data-text="1,200">1,200</span></span></div>
                          <div class="col" style="display: none;"><span class="match-reward">1 Seat</span></div>
                        </div>
                       
                      </div>

Нужно написать код на  PHP 8.2, используя XPath распаристь данные и добавить имеющийся файл json вот так:
{
"prize": [
        {
            "place": 1,
            "prize": 1866.66
        },
        {
            "place": 2,
            "prize": 1,466.66
        },
        {
            "place": 3,
            "prize": 1,200
        },
    ],
}