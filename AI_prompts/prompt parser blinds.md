Есть фрагмент html:
<div class="blind-comp-wrap">
    <div class="row header" data-blind="37">
        <div class="col">Lv</div>
        <div class="col">Blinds</div>
        <div class="col">Ante</div>
        <div class="col">TimeBank</div>
        <div class="col">Mins</div>
    </div>
    <div class="row data blind-gray">
        <div class="col"><i>1</i><span class="purple">Re-Entry</span></div>
        <div class="col">50/100</div>
        <div class="col">15</div>
        <div class="col">15s x 2</div>
        <div class="col">7 </div>
    </div>
    <div class="row data blind-gray">
        <div class="col"><i>2</i><span class="purple">Re-Entry</span></div>
        <div class="col">60/120</div>
        <div class="col">18</div>
        <div class="col">-</div>
        <div class="col">7 </div>
    </div>
    <div class="row data blind-gray">
        <div class="col"><i>3</i><span class="purple">Re-Entry</span></div>
        <div class="col">70/140</div>
        <div class="col">21</div>
        <div class="col">-</div>
        <div class="col">7 </div>
    </div>
</div>

Нужно написать код на PHP 8.2, используя XPath распаристь данные и добавить имеющийся файл json вот так:
{
"blinds": [
{
"level": 1,
"blind": "50/100",
"ante": 15,
"timebank": "15s x 2",
"mins": 7
},
{
"level": 2,
"blind": "60/120",
"ante": 18,
"timebank": "-",
"mins": 7
},
{
"level": 3,
"blind": "70/140",
"ante": 21,
"timebank": "-",
"mins": 7
},

}