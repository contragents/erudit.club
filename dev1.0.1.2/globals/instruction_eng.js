//
instruction = `
                            <h2 id="nav1">Об игре</h2>
                            <p>Эрудит на английском &mdash; настольная игра со словами, в которую могут играть от 2 до 4 человек, выкладывая слова из имеющихся у них букв на поле размером 15x15.</p>
                            <div class="fon-right">
                                <h2 id="nav2">Игровое поле</h2>
                                <p>Игровое поле состоит из 15х15, то есть 225 квадратов, на которые участники игры выкладывают буквы, составляя тем самым&nbsp;слова. В начале игры каждый игрок получает 7 случайных букв. 
                                <p>На середину игрового поля выкладывается первое&nbsp;слово. К этому слову по возможности, нужно приставить осташиеся буквы так, чтобы на пересечении получились новые слова.</p>
                                <p>Затем следующий игрок должен выставить свои&nbsp;буквы&nbsp;&laquo;на пересечение&raquo; или приставить их к уже составленным словам.</p>
                                <p>Слова&nbsp;выкладываются либо слева направо, либо сверху вниз.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav3">Словарь</h2>
                                <p>Разрешается использовать все&nbsp;слова, приведенные в кэмбриджском англо-русском словаре (https://dictionary.cambridge.org/ru/ ), включая наиболее <a href="#" onclick="$('#abbr').css({ display: 'block' });return false;" style="cursor: pointer;" title="ad AGM AIDS ATM BA BBC BSc BSE CCTV CD CEO CFC Corp dab DIY DNA DVD EFL ELT er ESL FA FAQ FM GCSE GDP GMO GMT GNP GP GPS HIV HQ ICT IOU IPA IQ ISP it ITV IVF JP JPEG LAN LCD LPG MA MBA MEP MP MPV MRI MRSA Ms MSc MTV NATO OAP PC PDA PE pin POW PR pt QC ram RSI SARS SASE SATNAV SGML SIDS SMS SPF SUV TB TEFL TESOL TV UFO UK USA VAT VCR VDU VIP WC WMD www XML">употребительные аббревиатуры</a>.<span id="abbr" style="display:none;">ad AGM AIDS ATM BA BBC BSc BSE CCTV CD CEO CFC Corp dab DIY DNA DVD EFL ELT er ESL FA FAQ FM GCSE GDP GMO GMT GNP GP GPS HIV HQ ICT IOU IPA IQ ISP it ITV IVF JP JPEG LAN LCD LPG MA MBA MEP MP MPV MRI MRSA Ms MSc MTV NATO OAP PC PDA PE pin POW PR pt QC ram RSI SARS SASE SATNAV SGML SIDS SMS SPF SUV TB TEFL TESOL TV UFO UK USA VAT VCR VDU VIP WC WMD www XML</span></p>
                                <p>Разрешено использовать только нарицательные имена существительные в единственном числе (либо во множественном при отсутствии у слова формы единственного числа).</p>
                                <p>Чтобы посмотреть, какие слова составили игроки в предыдущих ходах, а также узнать их значение и &laquo;стоимость&raquo;, кликните на кнопку <img src="/img/otjat/log.svg" height="64"/></p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav4">Ход игры</h2>
                                <p>В начале игры каждому дается по 7 фишек. За один ход можно выложить несколько&nbsp;слов. Каждое новое&nbsp;слово&nbsp;должно соприкасаться (иметь общую букву или буквы) с ранее выложенными&nbsp;словами.&nbsp;Слова&nbsp;читаются только по горизонтали слева направо и по вертикали сверху вниз.</p>
                                <p>Первое выложенное&nbsp;слово&nbsp;должно проходить через центральную клетку.</p>
                                <p>
                                Отправить свою комбинацию можно, нажав кнопку <br /><img src="/img/otjat/otpravit.svg" width="80%"/>
                                <br />
                                Если в данный момент ход не Ваш - кнопка станет неактивной <br /><img src="/img/inactive/otpravit.svg" width="80%"/>
                                <br />
                                Если кнопка ОТПРАВИТЬ начала мигать красным - время Вашего хода заканчивается. Скорее отправляйте свою комбинацию!
                                </p>
                                <p>Если игрок не хочет или не может выложить ни одного слова, - он имеет право поменять любое количество своих букв, пропустив при этом ход.
                                <br /><img src="/img/otjat/pomenyat.svg" width="80%"/>
                                </p>
                                <p>Любая последовательность букв по горизонтали и вертикали должна являться&nbsp;словом. Т.е. в игре не допускается появление на поле случайных буквосочетаний, не представляющих собою&nbsp;слов, соответствующих вышеприведенным критериям.</p>
                                <p>После каждого хода необходимо добрать новых букв до 7.</p>
                                <p>Если за ход игрок использовал все 7 букв, то ему начисляются дополнительные 15 очков.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav5">Распределение фишек и стоимость букв</h2>
                                <table cellpadding="10" cellspacing="10">
                                    <tbody>
                                        <tr>
                                            <th>Буква</th>
                                            <th>Кол-во</th>
                                            <th>Цена</th>
                                        </tr>
                                        <tr>
                                            <td><strong>*</strong></td>
                                            <td>10 шт.</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>A</td>
                                            <td>9 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>B</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>C</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>D</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>E</td>
                                            <td>12 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>F</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>G</td>
                                            <td>3 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>H</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>I</td>
                                            <td>9 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>J</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>K</td>
                                            <td>1 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>L</td>
                                            <td>4 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>M</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>N</td>
                                            <td>6 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>O</td>
                                            <td>8 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>P</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Q</td>
                                            <td>1 шт.</td>
                                            <td>10 очков</td>
                                        </tr>
                                        <tr>
                                            <td>R</td>
                                            <td>6 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>S</td>
                                            <td>4 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>T</td>
                                            <td>6 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>U</td>
                                            <td>4 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>V</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>W</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>X</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Y</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Z</td>
                                            <td>1 шт.</td>
                                            <td>10 очков</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav6">Подсчет очков и бонусы</h2>
                                <p>Каждой букве присвоено количество очков от 1 до 10. Некоторые квадраты на доске раскрашены в разные цвета. Количество очков, получаемых игроком за выложенное слово, подсчитывается следующим образом:</p>
                                <ul>
                                    <li>Если квадрат под буквой бесцветен, добавляется количество очков, написанное на букве</li>
                                    <li>Если квадрат <span style="background-color:green;color:white;">зеленый</span> - количество очков <strong>буквы</strong> умножается на <strong>2</strong></li>
                                    <li>Если квадрат <span style="background-color:yellow;color:black;">желтый</span> - количество очков <strong>буквы</strong> умножается на <strong>3</strong></li>
                                    <li>Если квадрат <span style="background-color:blue;color:white;">синий</span> - количество очков всего <strong>слова</strong> умножается на <strong>2</strong></li>
                                    <li>Если квадрат <span style="background-color:red;color:white;">красный</span> - количество очков всего <strong>слова</strong> умножается на <strong>3</strong></li>
                                </ul>
                                <p>Если слово использует множители обоего типа, то в удвоении (утроении) очков слова учитывается удвоение (утроение) очков букв.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav7">Звёздочка</h2>
                                <p>Также, в наборе фишек присутствуют три звёздочки. Такая фишка может быть использована как любая буква на выбор игрока. Например, игрок может выставить слово &laquo;P*ONE&raquo;, где роль буквы &laquo;H&raquo; будет играть звездочка.</p>
                                <p>Как только игрок выставит на поле звездочку, игра сразу предложит выбрать заменяемую ею букву. При перестановке звездочки выбор буквы будет предлагаться вновь.</p>
                                <p>Звездочка приносит столько очков, сколько бы принесла буква, роль которой она играет.&nbsp;</p>
                                <h3>Повторное использование звёздочки&nbsp;</h3>
                                <p>Если у любого из игроков есть буква, которую заменяет звёздочка на игровом поле, то он может заменить эту звёздочку своей буквой и использовать полученную звёздочку для составления слова, но только в текущий ход. Забрать звёздочку с поля "про запас" себе нельзя.</p>
                            </div>
`;