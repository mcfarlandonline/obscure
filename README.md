# obscure
Obscures everything between the tags {exp:obscure}{/exp:obscure} by encoding the contents similarly to the way the <a href="https://docs.expressionengine.com/latest/templates/globals/single-variables.html#encode" target="_blank" rel="noopener">{encode} tag for email</a> works in <a href="https://github.com/ExpressionEngine/ExpressionEngine">ExpressionEngine.</a> Add-on provides additional flexibility by allowing you to include other elements or images within the tag. For example:

    <li>
      {exp:obscure}
        <a href="mailto:megan@netraising.com?subject=I can fix Obscure Add-On">
          <img src="...envelope-icon.svg" alt="Email Megan" />
        </a>
       {/exp:obscure}
    </li>
