<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore {
    interface cache_cleaning_after_install { # indicates that the class should clear own cache after install any new module
        static function cache_cleaning();
    }
}

namespace effcore {
    interface has_Data_cache { # indicates that the cache should be separated by files
        static function not_external_properties_get();
    }
}

namespace effcore {
    interface has_postconstructor { # indicates that the __construct() should be called after the data load
    }
}

namespace effcore {
    interface has_postinit { # indicates that the _postinit() should be called after the data load
    }
}

namespace effcore {
    interface has_postparse { # indicates that the _postparse() should be called after parse the data
    }
}
