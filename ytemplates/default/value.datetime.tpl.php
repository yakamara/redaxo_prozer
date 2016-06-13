
<div id="<?= $this->getHTMLId() ?>" class="yform1 data yform-datetime">
    <div class="flabel">
        <label for="<?= $this->getFieldId() ?>" class="<?= $this->getWarningClass() ?>"><?= $this->getLabel() ?></label>
    </div>
    <div class="felement">
    <?php
        foreach ($format as $component):
            switch ($component):
                case '###Y###':
                    ?><select id="<?php echo $this->getFieldId('year') ?>" name="<?php echo $this->getFieldName('year') ?>" class="yform-select-datetime-year <?php echo $this->getWarningClass() ?>" size="1">
                        <option value="00">--</option>
                        <?php for ($i = $yearStart; $i <= $yearEnd; ++$i): ?>
                            <option value="<?php echo $i ?>"<?php echo $year == $i ? ' selected="selected"' : '' ?>><?php echo $i ?></option>
                        <?php endfor ?>
                    </select><?php
                    break;

                case '###M###':
                    ?><select id="<?php echo $this->getFieldId('month') ?>" name="<?php echo $this->getFieldName('month') ?>" class="yform-select-datetime-month <?php echo $this->getWarningClass() ?>" size="1">
                        <option value="00">--</option>
                        <?php for ($i = 1; $i < 13; ++$i): ?>
                            <option value="<?php echo $i ?>"<?php echo $month == $i ? ' selected="selected"' : '' ?>><?php echo $i ?></option>
                        <?php endfor ?>
                    </select><?php
                    break;

                case '###D###':
                    ?><select id="<?php echo $this->getFieldId('day') ?>" name="<?php echo $this->getFieldName('day') ?>" class="yform-select-datetime-day <?php echo $this->getWarningClass() ?>" size="1">
                        <option value="00">--</option>
                        <?php for ($i = 1; $i < 32; ++$i): ?>
                            <option value="<?php echo $i ?>"<?php echo $day == $i ? ' selected="selected"' : '' ?>><?php echo $i ?></option>
                        <?php endfor ?>
                    </select><?php
                    break;

                case '###H###':
                    ?><select id="<?php echo $this->getFieldId('hour') ?>" name="<?php echo $this->getFieldName('hour') ?>" class="yform-select-datetime-hour <?php echo $this->getWarningClass() ?>" size="1">
                    <?php foreach ($hours as $i): ?>
                        <option value="<?php echo $i ?>"<?php echo $hour == $i ? ' selected="selected"' : '' ?>><?php echo $i ?></option>
                    <?php endforeach ?>
                    </select><?php
                    break;

                case '###I###':
                    ?><select id="<?php echo $this->getFieldId('min') ?>" name="<?php echo $this->getFieldName('min') ?>" class="yform-select-datetime-minute <?php echo $this->getWarningClass() ?>" size="1">
                    <?php foreach ($minutes as $i): ?>
                        <option value="<?php echo $i ?>"<?php echo $minute == $i ? ' selected="selected"' : '' ?>><?php echo $i ?></option>
                    <?php endforeach ?>
                    </select><?php
                    break;

                default:
                    echo $component;
            endswitch;
        endforeach;
    ?>
    </div>
</div>
