$(function() {
    $( "#datepicker" ).datepicker();
    $( "#anim" ).change(function() {
      $( "#datepicker" ).datepicker( "option", "showAnim", $( this ).val() );
    });
  });



  // Change date format for MySQL
    // Resource: http://jsfiddle.net/gaby/WArtA/
    // Resource: http://stackoverflow.com/questions/7500058/how-to-change-date-format-mm-dd-yy-to-yyyy-mm-dd-in-date-picker
    // $(function(){
    //       $("#end_date").datepicker({ dateFormat: 'yy-mm-dd' });
    //       $("#start_date").datepicker({ dateFormat: 'yy-mm-dd' }).bind("change",function(){
    //           var minValue = $(this).val();
    //           minValue = $.datepicker.parseDate("yy-mm-dd", minValue);
    //           minValue.setDate(minValue.getDate()+1);
    //           $("#end_date").datepicker( "option", "minDate", minValue );
    //       })
    //   });
