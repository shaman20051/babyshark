Есть фрагмент html:
<div class="competition-player">
    <div class="player-comp-wrap" style="min-height: auto; opacity: 1;">
        <div class="row header">
            <div class="col competition-rank-col">Rank </div>
            <div class="col competition-name-col">Players</div>
            <div class="col competition-chips-col">Chips</div>
        </div>
        <div class="row data  leave ">
            <div class="col competition-rank-col">1</div>
            <div class="col competition-name-col">
                <div class="national-flag-icon tw-relative tw-grid tw-h-[24px] tw-items-center"
                    style="aspect-ratio: 3 / 2;">
                    <div class="tw-relative tw-h-full tw-w-full tw-leading-[1]" aria-haspopup="dialog"
                        aria-expanded="false" aria-controls="radix-:rc:" data-state="closed">
                        <div
                            style="position: absolute; height: 36px; width: 54px; top: -9px; left: -13.5px; z-index: 1;">
                        </div><img class="national-png-icon !tw-h-full !tw-w-full" data-src="th"
                            src="./1500_finish_files/th-oasspAOZ.png"
                            style="filter: drop-shadow(rgba(0, 0, 0, 0) 0px 0px 0px);">
                    </div>
                </div>
                <div class="mtt-avatar-wrap mtt-avatar-wrap--can-click"><img
                        class="mtt-avatar mtt-avatar-border-hall-0 mtt-avatar--hover-modal no-js-resize,m_fill,w_200,h_200"
                        src="./1500_finish_files/43b1302bafb730a2c68a4ac9e251f850"><noscript></noscript></div>
                <span style="color: rgb(240, 215, 75);">G.DraGon</span>
            </div>
            <div class="col competition-chips-col">4.09M</div>
        </div>
        <div class="row data  leave ">
            <div class="col competition-rank-col">2</div>
            <div class="col competition-name-col">
                <div class="national-flag-icon tw-relative tw-grid tw-h-[24px] tw-items-center"
                    style="aspect-ratio: 3 / 2;">
                    <div class="tw-relative tw-h-full tw-w-full tw-leading-[1]" aria-haspopup="dialog"
                        aria-expanded="false" aria-controls="radix-:rd:" data-state="closed">
                        <div
                            style="position: absolute; height: 36px; width: 54px; top: -9px; left: -13.5px; z-index: 1;">
                        </div><img class="national-png-icon !tw-h-full !tw-w-full" data-src="ru"
                            src="./1500_finish_files/ru-B2vrvMw0.png"
                            style="filter: drop-shadow(rgba(0, 0, 0, 0) 0px 0px 0px);">
                    </div>
                </div>
                <div class="mtt-avatar-wrap mtt-avatar-wrap--can-click"><img
                        class="mtt-avatar mtt-avatar-border-hall-0 mtt-avatar--hover-modal no-js-resize,m_fill,w_200,h_200"
                        src="./1500_finish_files/460f602e75f004fa6227f12122bfbd3c"><noscript></noscript></div>
                <span style="color: rgb(240, 215, 75);">ST1.Armada</span>
            </div>
            <div class="col competition-chips-col">Finish</div>
        </div>
        <div class="row data  leave ">
            <div class="col competition-rank-col">3</div>
            <div class="col competition-name-col">
              <div class="national-flag-icon tw-relative tw-grid tw-h-[24px] tw-items-center"
                style="aspect-ratio: 3 / 2;">
                <div class="tw-relative tw-h-full tw-w-full tw-leading-[1]" aria-haspopup="dialog"
                  aria-expanded="false" aria-controls="radix-:re:" data-state="closed">
                  <div
                    style="position: absolute; height: 36px; width: 54px; top: -9px; left: -13.5px; z-index: 1;">
                  </div><img class="national-png-icon !tw-h-full !tw-w-full" data-src="th"
                    src="./1500_finish_files/th-oasspAOZ.png"
                    style="filter: drop-shadow(rgba(0, 0, 0, 0) 0px 0px 0px);">
                </div>
              </div>
              <div class="mtt-avatar-wrap mtt-avatar-wrap--can-click"><img
                  class="mtt-avatar mtt-avatar-border-hall-0 mtt-avatar--hover-modal no-js-resize,m_fill,w_200,h_200"
                  src="./1500_finish_files/bade918ffb51e9e3184641927514d861"><noscript></noscript>
                <div class="mtt-avatar-vip mtt-avatar-vip1"></div>
              </div><span style="color: rgb(240, 215, 75);">chocolate</span>
            </div>
            <div class="col competition-chips-col">Finish</div>
          </div>

    </div>
</div>

    Нужно написать код на PHP 8.2, используя XPath распаристь данные и добавить имеющийся файл json вот так:
    {
        "players": [
        {
            "rank": 1,
            "player": {
                "country": "th",
                "avatar": "./1500_finish_files/43b1302bafb730a2c68a4ac9e251f850",
                "nickname": "G.DraGon",
            }
            "chips": 4090000
        },
        {
            "rank": 2,
            "player": {
                "country": "ru",
                "avatar": ./1500_finish_files/460f602e75f004fa6227f12122bfbd3c",
                "nickname": "ST1.Armada",
            }
            "chips": "Finished"
        },
        {
            "rank": 3,
            "player": {
                "country": "th",
                "avatar": "./1500_finish_files/bade918ffb51e9e3184641927514d861",
                "nickname": "chocolate"
            },
            "chips": "Finished"
        },
    }