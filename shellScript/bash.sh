
if ! grep ".vinillarc" ~/.bashrc
then
cp ./shellScript/bash.source ~/.vinillarc

interpretator=`cat ./$type/interpretator`
alias="alias vinilla_$type=\"$interpretator $folder/$type/vinilla`cat ./$type/extension`\""
echo "bash alias will be '$alias'"

echo $alias >> ~/.vinillarc
echo "
_vinilla_completions()
{
    bins=\`vinilla_$type bins\`
    COMPREPLY=(\$(compgen -W \"\$bins\" \"\${COMP_WORDS[1]}\"))
#    for bin in \$bins
#    do
#        COMPREPLY+=(\"\$bin\")
#    done
}

" >>  ~/.vinillarc
echo "complete -F _vinilla_completions vinilla_$type" >> ~/.vinillarc

echo "source \$HOME/.vinillarc" >> ~/.bashrc
fi




